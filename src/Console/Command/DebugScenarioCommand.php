<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Console\Command;

use Stateforge\Scenario\Core\Console\Input\Argument;
use Stateforge\Scenario\Core\Console\Input\InputType;
use Stateforge\Scenario\Core\Contract\CliInput;
use Stateforge\Scenario\Core\Contract\CliOutput;
use Stateforge\Scenario\Core\PHPUnit\Finder\ScenarioTestFinder;
use Stateforge\Scenario\Core\Runtime\Application\TestClassState;
use Stateforge\Scenario\Core\Runtime\Application\TestMethodState;
use Stateforge\Scenario\Core\Runtime\Metadata\AttributeContext;
use Stateforge\Scenario\Core\Runtime\Metadata\AttributeProcessor;
use Stateforge\Scenario\Core\Runtime\Metadata\ExecutionType;
use Stateforge\Scenario\Core\Runtime\Metadata\Parser\ClassAttributeParser;
use Stateforge\Scenario\Core\Runtime\Metadata\Parser\MethodAttributeParser;
use Stateforge\Scenario\Core\Runtime\ScenarioDefinition;
use Stateforge\Scenario\Core\Runtime\ScenarioRegistry;
use function array_keys;
use function array_shift;
use function array_unique;
use function array_values;
use function count;
use function is_string;
use function sprintf;

final class DebugScenarioCommand extends CliCommand
{
    public function __construct(private ScenarioTestFinder $finder)
    {
    }

    public function description(): string
    {
        return 'Debugs a given scenario or Unit test.';
    }

    protected function define(CliInput $input): void
    {
        $input->defineArgument(new Argument('class', InputType::String));
        $input->defineArgument(new Argument('method', InputType::String));
    }

    protected function execute(CliInput $input, CliOutput $output): Command
    {
        $scenarioDefinitions = ScenarioRegistry::getInstance()->all();
        $testClasses = $this->finder->all();

        $directDebug = $this->handleInput($input, $output, $scenarioDefinitions, $testClasses);
        if ($directDebug !== null) {
            return $directDebug === true
                ? Command::Success
                : Command::Error;
        }

        $type = $this->getSelectedType($output, $scenarioDefinitions, $testClasses);
        if ($type === false) {
            $output->error('No scenarios or unit tests were found, please create one.');
            return Command::Error;
        }

        return match($type) {
            'Scenario' => $this->debugScenario($scenarioDefinitions, $output),
            'Unit Test' => $this->debugTest($testClasses, $output),
        };
    }

    /**
     * @param array<class-string|string, ScenarioDefinition> $scenarioDefinitions
     * @param array<class-string, list<non-empty-string>> $classesMethods
     */
    private function handleInput(CliInput $input, CliOutput $output, array $scenarioDefinitions, array $classesMethods): bool|null
    {
        $className = $input->argument('class');
        $method = $input->argument('method');

        if ($className === null
            && $method === null) {
            return null;
        }

        if (is_string($className) === true) {
            if (isset($scenarioDefinitions[$className]) === true) {
                $this->runDebugScenario($output, $scenarioDefinitions[$className]);
                return true;
            }

            if (isset($classesMethods[$className]) === true) {
                $testMethod = null;
                if (is_string($method) === true) {
                    $testMethod = $method;
                }

                /** @var class-string $className */
                $this->runDebugTest($output, $className, $testMethod);
            }

            return false;
        }

        return null;
    }

    /**
     * @param array<class-string|string, ScenarioDefinition> $scenarioDefinitions
     * @param array<class-string, list<non-empty-string>> $testClasses
     * @return 'Scenario'|'Unit Test'|false
     */
    private function getSelectedType(CliOutput $output, array $scenarioDefinitions, array $testClasses): string|false
    {
        if (count($scenarioDefinitions) === 0
            && count($testClasses) === 0) {
            return false;
        }

        if (count($scenarioDefinitions) === 0) {
            return 'Unit Test';
        }

        if (count($testClasses) === 0) {
            return 'Scenario';
        }

        /** @var list<'Scenario'|'Unit Test'> $choices */
        $choices = ['Scenario', 'Unit Test'];
        return $choices[(int)$output->choice('Which kind of class would you like to debug?', $choices)];
    }

    /**
     * @param array<class-string|string, ScenarioDefinition> $scenarioDefinitions
     */
    private function debugScenario(array $scenarioDefinitions, CliOutput $output): Command
    {
        $scenarios = [];
        foreach ($scenarioDefinitions as $scenarioDefinition) {
            $scenarios[$scenarioDefinition->class . ' (' . $scenarioDefinition->suite . ')'] = $scenarioDefinition;
        }

        /** @var list<non-falsy-string> $options */
        $options = array_values(array_unique(array_keys($scenarios)));
        $choosen = (int)$output->choice('Which scenario would you like to debug?', $options);

        $scenario = $scenarios[$options[$choosen]];
        $this->runDebugScenario($output, $scenario);

        return Command::Success;
    }

    /**
     * @param array<class-string, list<non-empty-string>> $classesMethods
     */
    private function debugTest(array $classesMethods, CliOutput $output): Command
    {
        /** @var list<class-string> $testClasses */
        $testClasses = array_keys($classesMethods);

        if (count($testClasses) === 1) {
            $testClass = array_shift($testClasses);
        } else {
            /** @var class-string $testClass */
            $testClass = $testClasses[(int)$output->choice('Which class would you like to debug?', $testClasses)];
        }

        /** @var list<non-empty-string> $methods */
        $methods = $classesMethods[$testClass];

        if (count($methods) === 0) {
            $this->runDebugTest($output, $testClass, null);
            return Command::Success;
        }

        if (count($methods) === 1) {
            $method = array_shift($methods);
        } else {
            $method = $output->choice(sprintf('Which method would you like to debug from %s?', $testClass), $methods);
        }

        $this->runDebugTest($output, $testClass, $method);
        return Command::Success;
    }

    private function runDebugScenario(CliOutput $output, ScenarioDefinition $scenario): void
    {
        /** @var class-string $scenarioClass */
        $scenarioClass = $scenario->class;
        $output->headline($scenario->suite . ': ' . $scenarioClass);

        $output->table(
            null,
            [
                [ $scenario->attribute->name, $scenario->attribute->description ],
            ],
            null,
            false,
        );

        if (count($scenario->parameters) > 0) {
            $output->headline('The following parameters are defined:');

            $parameters = [];
            foreach ($scenario->parameters as $parameter) {
                $parameters[] = [
                    $parameter->name,
                    $parameter->type->value,
                    $parameter->description,
                    $parameter->required === true ? 'true' : 'false',
                    $parameter->repeatable === true ? 'true' : 'false',
                    $parameter->type->asString($parameter->default),
                ];
            }

            $output->table(
                [ 'name', 'type', 'description', 'required', 'repeatable', 'default' ],
                $parameters,
            );
        }

        $this->runDebugClass($output, $scenarioClass, ExecutionType::Up);
        $this->runDebugMethod($output, $scenarioClass, ExecutionType::Up->value, ExecutionType::Up);
        $this->runDebugClass($output, $scenarioClass, ExecutionType::Down);
        $this->runDebugMethod($output, $scenarioClass, ExecutionType::Down->value, ExecutionType::Down);
    }

    /**
     * @param class-string $testClass
     */
    private function runDebugTest(CliOutput $output, string $testClass, ?string $method): void
    {
        $this->runDebugClass($output, $testClass, ExecutionType::Up);
        if ($method !== null) {
            $this->runDebugMethod($output, $testClass, $method, ExecutionType::Up);
        }
        $this->runDebugClass($output, $testClass, ExecutionType::Down);
        if ($method !== null) {
            $this->runDebugMethod($output, $testClass, $method, ExecutionType::Down);
        }
    }

    /**
     * @param class-string $className
     */
    private function runDebugClass(CliOutput $output, string $className, ExecutionType $executionType): void
    {
        $context = AttributeContext::getInstance(
            TestClassState::class,
            null,
            $executionType,
            true,
            null,
        );
        (new AttributeProcessor())->process(
            $context,
            (new ClassAttributeParser())->parse($className),
        );

        $output->headline(sprintf('Audits from %s with execution %s', $className, $executionType->value));
        $this->renderContextAudit($context, $output);
    }

    /**
     * @param class-string $className
     */
    private function runDebugMethod(CliOutput $output, string $className, string $method, ExecutionType $executionType): void
    {
        $context = AttributeContext::getInstance(
            TestMethodState::class,
            $method,
            $executionType,
            true,
            null,
        );
        (new AttributeProcessor())->process(
            $context,
            (new MethodAttributeParser())->parse($className, $method),
        );

        $output->headline(sprintf('Audits from %s::%s with execution %s', $className, $method, $executionType->value));
        $this->renderContextAudit($context, $output);
    }

    private function renderContextAudit(AttributeContext $context, CliOutput $output): void
    {
        $output->writeln($context->getAudits());

        $classState = new TestClassState();
        $methodState = new TestMethodState();

        $classFailure = $classState->failure($context->class);
        if ($classFailure !== null) {
            $output->error($classFailure->getMessage());
        }

        if ($context->method !== null) {
            $methodFailure = $methodState->failure($context->class, $context->method);
            if ($methodFailure !== null) {
                $output->error($methodFailure->getMessage());
            }
        }
    }
}
