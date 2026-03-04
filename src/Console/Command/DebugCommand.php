<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Console\Command;

use Scenario\Core\Contract\CliInput;
use Scenario\Core\Contract\CliOutput;
use Scenario\Core\PHPUnit\Finder\ScenarioTestFinder;
use Scenario\Core\Runtime\Application\TestClassState;
use Scenario\Core\Runtime\Application\TestMethodState;
use Scenario\Core\Runtime\Metadata\AttributeContext;
use Scenario\Core\Runtime\Metadata\AttributeProcessor;
use Scenario\Core\Runtime\Metadata\ExecutionType;
use Scenario\Core\Runtime\Metadata\Parser\ClassAttributeParser;
use Scenario\Core\Runtime\Metadata\Parser\MethodAttributeParser;
use Scenario\Core\Runtime\ScenarioDefinition;
use Scenario\Core\Runtime\ScenarioRegistry;

final class DebugCommand extends CliCommand
{
    public function __construct(private ScenarioTestFinder $finder)
    {
    }

    public function description(): string
    {
        return 'Debugs a given scenario or Unit test.';
    }

    protected function execute(CliInput $input, CliOutput $output): Command
    {
        $scenarioDefinitions = ScenarioRegistry::getInstance()->all();
        $testClasses = $this->finder->all();

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

        /** @var class-string $scenarioClass */
        $scenarioClass = $scenarios[$options[$choosen]]->class;
        $this->runDebugClass($output, $scenarioClass, ExecutionType::Up);
        $this->runDebugMethod($output, $scenarioClass, 'up', ExecutionType::Up);
        $this->runDebugClass($output, $scenarioClass, ExecutionType::Down);
        $this->runDebugMethod($output, $scenarioClass, 'down', ExecutionType::Down);

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
            $testClass = $output->choice('Which class would you like to debug?', $testClasses);
        }

        /** @var list<non-empty-string> $methods */
        $methods = $classesMethods[$testClass];

        if (count($methods) === 0) {
            $this->runDebugClass($output, $testClass, ExecutionType::Up);
            $this->runDebugClass($output, $testClass, ExecutionType::Down);
            return Command::Success;
        }

        if (count($methods) === 1) {
            $method = array_shift($methods);
        } else {
            $method = $output->choice(sprintf('Which method would you like to debug from %s?', $testClass), $methods);
        }

        $this->runDebugClass($output, $testClass, ExecutionType::Up);
        $this->runDebugMethod($output, $testClass, $method, ExecutionType::Up);
        $this->runDebugClass($output, $testClass, ExecutionType::Down);
        $this->runDebugMethod($output, $testClass, $method, ExecutionType::Down);

        return Command::Success;
    }

    /**
     * @param class-string $className
     */
    private function runDebugClass(CliOutput $output, string $className, ExecutionType $executionType): void
    {
        $context = new AttributeContext(
            TestClassState::class,
            null,
            $executionType,
            true,
        );
        new AttributeProcessor()->process(
            $context,
            new ClassAttributeParser()->parse($className),
        );

        $output->headline(sprintf('Audits from %s with execution %s', $className, $executionType->value));
        $this->renderContextAudit($context, $output);
    }

    /**
     * @param class-string $className
     */
    private function runDebugMethod(CliOutput $output, string $className, string $method, ExecutionType $executionType): void
    {
        $context = new AttributeContext(
            TestMethodState::class,
            $method,
            $executionType,
            true,
        );
        new AttributeProcessor()->process(
            $context,
            new MethodAttributeParser()->parse($className, $method),
        );

        $output->headline(sprintf('Audits from %s::%s with execution %s', $className, $method, $executionType->value));
        $this->renderContextAudit($context, $output);
    }

    private function renderContextAudit(AttributeContext $context, CliOutput $output): void
    {
        $output->writeln(array_reverse($context->getAudits()));

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
