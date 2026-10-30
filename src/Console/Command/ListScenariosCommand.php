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

use Stateforge\Scenario\Core\Console\Input\InputType;
use Stateforge\Scenario\Core\Console\Input\Option;
use Stateforge\Scenario\Core\Console\ScenarioSuiteFilter;
use Stateforge\Scenario\Core\Contract\CliInput;
use Stateforge\Scenario\Core\Contract\CliOutput;
use Stateforge\Scenario\Core\Runtime\Application;
use Stateforge\Scenario\Core\Runtime\ScenarioRegistry;
use function array_values;
use function count;
use function is_string;

final class ListScenariosCommand extends CliCommand
{
    public function description(): string
    {
        return 'List all available scenarios, use --suite="name of you suite" if you want to see just one suite.';
    }

    protected function define(CliInput $input): void
    {
        $input->defineOption(new Option('suite', InputType::String));
    }

    protected function execute(CliInput $input, CliOutput $output): Command
    {
        $suite = $input->option('suite');
        if ($suite !== null
            && is_string($suite) === false) {
            $suite = null;
        }

        $scenarios = (new ScenarioSuiteFilter())->filter(ScenarioRegistry::getInstance()->all(), $suite);
        if (count($scenarios) === 0) {
            $output->warn('No scenarios found. Please create one.');
        }

        $tables = [];
        foreach ($scenarios as $scenario) {
            if (isset($tables[$scenario->suite]) === false) {
                $tables[$scenario->suite] = [];
            }
            $tables[$scenario->suite][$scenario->class] = [
                $scenario->class,
                $scenario->attribute->name,
                $scenario->attribute->description,
            ];
        }

        foreach ($tables as $suite => $table) {
            $tables[$suite] = array_values($table);
        }

        $suites = Application::config()?->getSuites() ?? [];
        foreach ($tables as $suite => $definitions) {
            $definition = $suites[$suite] ?? null;
            $headline = $definition === null || $definition->description === ''
                ? $suite
                : $definition->description . ' (' . $definition->name . ')';
            $output->headline($headline);
            $output->table(
                ['class', 'name', 'description'],
                $definitions,
            );
        }
        return Command::Success;
    }
}
