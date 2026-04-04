<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Console;

use Stateforge\Scenario\Core\Console\Command\ApplyScenarioCommand;
use Stateforge\Scenario\Core\Console\Command\Command;
use Stateforge\Scenario\Core\Console\Command\DebugScenarioCommand;
use Stateforge\Scenario\Core\Console\Command\InstallScenarioCommand;
use Stateforge\Scenario\Core\Console\Command\ListCommands;
use Stateforge\Scenario\Core\Console\Command\ListScenariosCommand;
use Stateforge\Scenario\Core\Console\Command\MakeScenarioCommand;
use Stateforge\Scenario\Core\Console\Command\RefreshDatabaseCommand;
use Stateforge\Scenario\Core\Console\Output\NativeTerminalIO;
use Stateforge\Scenario\Core\Console\Output\SystemTerminal;
use Stateforge\Scenario\Core\Console\Output\Theme\AnsiStyler;
use Stateforge\Scenario\Core\PHPUnit\Finder\ScenarioTestFinder;
use Stateforge\Scenario\Core\Runtime\Application;
use function define;
use function defined;
use const PHP_EOL;

final class CliApplication
{
    /**
     * @param list<string> $inputArgs
     */
    public function run(array $inputArgs): int
    {
        (new Application())->bootstrap();

        if (Application::isBooted() === false
            && defined('SCENARIO_CLI_DISABLED') === false) {
            define('SCENARIO_CLI_DISABLED', true);
        }

        if (defined('SCENARIO_CLI_DISABLED') === false) {
            define('SCENARIO_CLI_DISABLED', false);
        }

        $input = new Input($inputArgs);

        if ($input->force() !== true
            && SCENARIO_CLI_DISABLED === true) {
            echo 'CLI DISABLED!' . PHP_EOL;
            return Command::Error->value;
        }

        $commands = [
            'apply' => new ApplyScenarioCommand(),
            'debug' => new DebugScenarioCommand(new ScenarioTestFinder()),
            'install' => new InstallScenarioCommand(),
            'list' => new ListScenariosCommand(),
            'make' => new MakeScenarioCommand(),
            'refresh' => new RefreshDatabaseCommand(),
        ];

        $output = new Output(new AnsiStyler(new SystemTerminal()), new NativeTerminalIO());

        if ($input->command() !== null
            && isset($commands[$input->command()]) === true) {
            return $commands[$input->command()]
                ->run(
                    $input,
                    $output,
                )->value;
        }

        return (new ListCommands($commands))
            ->run(
                $input,
                $output,
            )->value;
    }
}
