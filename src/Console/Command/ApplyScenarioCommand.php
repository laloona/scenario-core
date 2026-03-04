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
use Scenario\Core\Runtime\Application\TestClassState;
use Scenario\Core\Runtime\Application\TestMethodState;
use Scenario\Core\Runtime\Metadata\AttributeContext;
use Scenario\Core\Runtime\Metadata\AttributeProcessor;
use Scenario\Core\Runtime\Metadata\ExecutionType;
use Scenario\Core\Runtime\Metadata\Parser\ClassAttributeParser;
use Scenario\Core\Runtime\Metadata\Parser\MethodAttributeParser;
use Scenario\Core\Runtime\ScenarioRegistry;

final class ApplyScenarioCommand extends CliCommand
{
    public function description(): string
    {
        return 'Applies a given scenario, use --up or --down to choose how the scenario should be applied.';
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
        $executionType = ExecutionType::Up;
        if (is_string($scenario) === true) {
            $scenarioClass = null;
            foreach ($scenarioDefinitions as $scenarioDefinition) {
                if ($scenarioDefinition->class === $scenario) {
                    $scenarioClass = $scenarioDefinition->class;
                    break;
                }
            }

            if ($scenarioClass === null) {
                $scenario = null;
                $output->error(sprintf('Given scenario [%s] is not registered.', $input->argument('0')));
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
                $output->error('Invalid scenario selection.');
                return Command::Error;
            }

            $scenario = $scenarios[$options[$choosen]]->class;
        }

        /** @var class-string $scenario */
        $this->applyScenario(
            $scenario,
            $input->option('down') === true ? ExecutionType::Down : ExecutionType::Up,
        );

        (new TestClassState())->throw(__CLASS__);
        (new TestMethodState())->throw(__CLASS__, __METHOD__);

        $output->success('Scenario "' . $scenario . '::' . $executionType->value . '" was applied successfully.');
        return Command::Success;
    }

    /**
     * @param class-string $className
     */
    private function applyScenario(string $className, ExecutionType $executionType): void
    {
        (new AttributeProcessor())->process(
            new AttributeContext(
                __CLASS__,
                null,
                $executionType,
                false,
            ),
            (new ClassAttributeParser())->parse($className),
        );

        (new AttributeProcessor())->process(
            new AttributeContext(
                __CLASS__,
                __METHOD__,
                $executionType,
                false,
            ),
            (new MethodAttributeParser())->parse($className, $executionType->value),
        );
    }
}
