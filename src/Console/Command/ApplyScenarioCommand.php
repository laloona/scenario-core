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
use Scenario\Core\Contract\CliInput;
use Scenario\Core\Contract\CliOutput;
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
        return 'Executes a given scenario, use --up or --down to choose how the scenario should be executed.';
    }

    protected function execute(CliInput $input, CliOutput $output): Command
    {
        if ($input->option('up') !== null
            && $input->option('down') !== null) {
            $output->error('You can just use either up or down scenarios.');

            return Command::Error;
        }

        $scenarioDefinitions = ScenarioRegistry::getInstance()->all();
        if (count($scenarioDefinitions) === 0) {
            $output->error('No scenarios were found, please create one.');
            return Command::Error;
        }

        $scenario = $input->argument('0');
        if ($scenario === null
            && $input->option('quiet') === true) {
            $output->error('No scenario was given to execute.');
            return Command::Error;
        }

        $executionType = ExecutionType::Up;
        if (is_string($scenario) === true) {
            try {
                $executionType = $input->option('down') === true ? ExecutionType::Down : ExecutionType::Up;
                $this->applyScenario(
                    $scenario,
                    $executionType,
                );

                new TestMethodState()->throw(__CLASS__, __METHOD__);

                $output->success('Scenario "' . $scenario . '::' . $executionType->value . '" was executed successfully.');
                return Command::Success;
            } catch (RegistryException $exception) {
                $output->error(sprintf('Given scenario [%s] is not registered.', $input->argument('0')));

                if ($input->option('quiet') === true) {
                    return Command::Error;
                }
            }
        }

        $scenarios = [];
        foreach ($scenarioDefinitions as $scenarioDefinition) {
            $scenarios[$scenarioDefinition->class . ' (' . $scenarioDefinition->suite . ')'] = $scenarioDefinition;
        }

        $options = array_values(array_unique(array_keys($scenarios)));
        $choosen = (int)$output->choice('Which scenario would you like to apply?', $options);
        $this->applyScenario(
            $scenarios[$options[$choosen]]->class,
            $input->option('down') === true ? ExecutionType::Down : ExecutionType::Up,
        );

        new TestMethodState()->throw(__CLASS__, __METHOD__);

        $output->success('Scenario "' . $scenario . '::' . $executionType->value . '" was applied successfully.');
        return Command::Success;
    }

    private function applyScenario(string $name, ExecutionType $executionType): void
    {
        $scenario = ScenarioRegistry::getInstance()->resolve($name);
        HandlerRegistry::getInstance()
            ->attributeHandler(ApplyScenario::class)
            ->handle(
                new AttributeContext(
                    __CLASS__,
                    __METHOD__,
                    $executionType,
                ),
                $scenario->attribute,
            );
    }
}
