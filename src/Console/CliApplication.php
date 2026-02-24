<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Console;

use Scenario\Core\Application;
use Scenario\Core\Console\Command\ApplyScenarioCommand;
use Scenario\Core\Console\Command\Command;
use Scenario\Core\Console\Command\ListCommands;
use Scenario\Core\Console\Command\ListScenariosCommand;
use Scenario\Core\Console\Command\MakeScenarioCommand;
use Scenario\Core\Console\Command\RefreshDatabaseCommand;

final class CliApplication
{
    /**
     * @param list<string> $inputArgs
     */
    public function run(array $inputArgs): int
    {
        new Application()->bootstrap();

        if (defined('SCENARIO_CLI_DISABLED') === false) {
            define('SCENARIO_CLI_DISABLED', false);
        }

        $input = new Input($inputArgs);

        if ($input->option('force') !== true
            && SCENARIO_CLI_DISABLED === true) {
            echo 'CLI DISABLED!' . PHP_EOL;
            return Command::Error->value;
        }

        $commands = [
            'list' => new ListScenariosCommand(),
            'execute' => new ApplyScenarioCommand(),
            'make' => new MakeScenarioCommand(),
            'refresh' => new RefreshDatabaseCommand(),
        ];

        if ($input->command() !== null
            && isset($commands[$input->command()]) === true) {
            return $commands[$input->command()]->run($input)->value;
        }

        return new ListCommands($commands)->run($input)->value;
    }
}
