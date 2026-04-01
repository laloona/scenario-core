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

use Scenario\Core\Attribute\ApplyScenario;
use Scenario\Core\Console\Input\Argument;
use Scenario\Core\Console\Input\InputType;
use Scenario\Core\Console\Input\Option;
use Scenario\Core\Console\Input\ParameterParser;
use Scenario\Core\Contract\CliInput;
use Scenario\Core\Contract\CliOutput;
use Scenario\Core\Runtime\Application\TestClassState;
use Scenario\Core\Runtime\Application\TestMethodState;
use Scenario\Core\Runtime\Exception\RegistryException;
use Scenario\Core\Runtime\Metadata\AttributeContext;
use Scenario\Core\Runtime\Metadata\ExecutionType;
use Scenario\Core\Runtime\Metadata\HandlerRegistry;
use Scenario\Core\Runtime\ScenarioRegistry;

final class ApplyScenarioCommand extends CliCommand
{
    public function description(): string
    {
        return 'Applies a given scenario, use --up or --down to choose how the scenario should be applied.';
    }

    protected function define(CliInput $input): void
    {
        $input->defineArgument(new Argument('scenario', InputType::String));
        $input->defineOption(new Option('audit', InputType::Boolean));
        $input->defineOption(new Option('up', InputType::Boolean));
        $input->defineOption(new Option('down', InputType::Boolean));
        $input->defineOption(new Option('parameter', InputType::String, false, true));
    }

    protected function execute(CliInput $input, CliOutput $output): Command
    {
        if ($input->option('up') !== null
            && $input->option('down') !== null) {
            if ($input->option('quiet') !== true) {
                $output->error('You can just use either up or down scenarios.');
            }
            return Command::Error;
        }

        $scenarioDefinitions = ScenarioRegistry::getInstance()->all();
        if (count($scenarioDefinitions) === 0) {
            if ($input->option('quiet') !== true) {
                $output->error('No scenarios were found, please create one.');
            }
            return Command::Error;
        }

        $directExecution = false;
        $scenario = $input->argument('scenario');
        $executionType = $input->option('down') === true ? ExecutionType::Down : ExecutionType::Up;
        if (is_string($scenario) === true) {
            try {
                $scenario = ScenarioRegistry::getInstance()->resolve($scenario)->class;
                $directExecution = true;
            } catch (RegistryException $exception) {
                $scenario = null;
                if ($input->option('quiet') !== true) {
                    $output->error(sprintf('Given scenario [%s] is not registered.', $input->argument('scenario')));
                }
            }
        }

        if ($scenario === null) {
            $scenarios = [];
            foreach ($scenarioDefinitions as $scenarioDefinition) {
                $scenarios[$scenarioDefinition->class . ' (' . $scenarioDefinition->suite . ')'] = $scenarioDefinition;
            }

            $options = array_keys($scenarios);
            $choosen = (int)$output->choice('Which scenario would you like to apply?', $options);
            if (isset($options[$choosen]) === false) {
                if ($input->option('quiet') !== true) {
                    $output->error('Invalid scenario selection.');
                }
                return Command::Error;
            }

            $scenario = $scenarios[$options[$choosen]]->class;
        }

        $parameters = [];
        if (is_string($scenario) === true) {
            $inputParameter = ($directExecution === true)
                ? (new ParameterParser())->parse($input->option('parameter'))
                : [];

            $definition = $scenarioDefinitions[$scenario];
            if (count($definition->parameters) > 0) {
                foreach ($definition->parameters as $parameter) {
                    if ($directExecution === true) {
                        if (array_key_exists($parameter->name, $inputParameter) === false) {
                            continue;
                        }

                        $parameters[$parameter->name] = ($parameter->repeatable === true && is_array($inputParameter[$parameter->name]) === false)
                            ? [ $inputParameter[$parameter->name] ]
                            : $inputParameter[$parameter->name];
                        continue;
                    }

                    $ask = sprintf(
                        'Please insert value for %s parameter "%s"%s%s',
                        $parameter->type->value,
                        $parameter->name,
                        $parameter->description === null ? '' : ' (' . $parameter->description . ')',
                        $parameter->required === true ? ' (required)' : '',
                    );
                    $validator = $parameter->required === true
                        ? fn ($input) => $parameter->type->valid($input)
                        : fn ($input) => $input === null || $parameter->type->valid($input);
                    $default = $parameter->asString($parameter->default);
                    $answer = $output->ask($ask, $default, $validator);
                    if ($parameter->repeatable === true) {
                        $value = [];
                        if ($answer !== null) {
                            $value[] = $answer;

                            while ($output->confirm('Do you want to continue?', false) === true) {
                                $answer = $output->ask($ask, $default, $validator);
                                if ($answer !== null) {
                                    $value[] = $answer;
                                    continue;
                                }
                                break;
                            }
                        }

                        $answer = $value;
                    }

                    $parameters[$parameter->name] = $answer;
                }
            }
        }

        /** @var class-string $scenario */
        $this->applyScenario(
            $input->option('audit') === true ? $output : null,
            $scenario,
            $executionType,
            $parameters,
        );

        (new TestClassState())->throw(__CLASS__);
        (new TestMethodState())->throw(__CLASS__, $executionType->value);

        if ($input->option('quiet') !== true) {
            $output->success('Scenario "' . $scenario . '::' . $executionType->value . '" was applied successfully.');
        }
        return Command::Success;
    }

    /**
     * @param class-string $className
     * @param array<string, mixed> $parameters
     */
    private function applyScenario(
        ?CliOutput $output,
        string $className,
        ExecutionType $executionType,
        array $parameters,
    ): void {
        HandlerRegistry::getInstance()
            ->attributeHandler(ApplyScenario::class)
            ->handle(
                AttributeContext::getInstance(
                    __CLASS__,
                    $executionType->value,
                    $executionType,
                    false,
                    $output,
                ),
                new ApplyScenario($className, $parameters),
            );
    }
}
