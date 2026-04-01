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

use Scenario\Core\Console\Input\InputType;
use Scenario\Core\Console\Input\Option;
use Scenario\Core\Contract\CliInput;
use Scenario\Core\Contract\CliOutput;
use Scenario\Core\Runtime\ScenarioRegistry;
use function array_values;
use function count;

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
        $scenarios = ScenarioRegistry::getInstance()->all();
        if (count($scenarios) === 0) {
            $output->warn('No scenarios found. Please create one.');
        }

        $filtered = [];
        if ($input->option('suite') !== null) {
            foreach ($scenarios as $name => $scenario) {
                if ($scenario->suite === $input->option('suite')) {
                    $filtered[$name] = $scenario;
                }
            }
            $scenarios = $filtered;
            unset($filtered);
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

        foreach ($tables as $suite => $definitions) {
            $output->headline($suite);
            $output->table(
                ['class', 'name', 'description'],
                $definitions,
            );
        }
        return Command::Success;
    }
}
