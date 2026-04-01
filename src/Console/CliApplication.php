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

use Scenario\Core\Console\Command\ApplyScenarioCommand;
use Scenario\Core\Console\Command\Command;
use Scenario\Core\Console\Command\DebugCommand;
use Scenario\Core\Console\Command\InstallScenarioCommand;
use Scenario\Core\Console\Command\ListCommands;
use Scenario\Core\Console\Command\ListScenariosCommand;
use Scenario\Core\Console\Command\MakeScenarioCommand;
use Scenario\Core\Console\Command\RefreshDatabaseCommand;
use Scenario\Core\Console\Output\NativeTerminalIO;
use Scenario\Core\Console\Output\SystemTerminal;
use Scenario\Core\Console\Output\Theme\AnsiStyler;
use Scenario\Core\PHPUnit\Finder\ScenarioTestFinder;
use Scenario\Core\Runtime\Application;
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
            'debug' => new DebugCommand(new ScenarioTestFinder()),
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
