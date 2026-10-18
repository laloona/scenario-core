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
use Stateforge\Scenario\Core\Console\Command\CliCommand;
use Stateforge\Scenario\Core\Console\Command\Command;
use Stateforge\Scenario\Core\Console\Command\DebugScenarioCommand;
use Stateforge\Scenario\Core\Console\Command\InstallScenarioCommand;
use Stateforge\Scenario\Core\Console\Command\ListCommands;
use Stateforge\Scenario\Core\Console\Command\ListParameterTypesCommand;
use Stateforge\Scenario\Core\Console\Command\ListScenariosCommand;
use Stateforge\Scenario\Core\Console\Command\MakeScenarioCommand;
use Stateforge\Scenario\Core\Console\Command\RefreshDatabaseCommand;
use Stateforge\Scenario\Core\Console\Output\NativeTerminalIO;
use Stateforge\Scenario\Core\Console\Output\SystemTerminal;
use Stateforge\Scenario\Core\Console\Output\Theme\AnsiStyler;
use Stateforge\Scenario\Core\Extension;
use Stateforge\Scenario\Core\PHPUnit\Finder\ScenarioTestFinder;
use Stateforge\Scenario\Core\Runtime\Application;
use Stateforge\Scenario\Core\Runtime\Application\ApplicationState;
use Stateforge\Scenario\Core\Runtime\Exception\InvalidExtensionSubClassException;
use function define;
use function defined;
use function fopen;
use function get_class;
use function is_subclass_of;
use const PHP_EOL;

final class CliApplication
{
    /**
     * @var array<string, CliCommand>
     */
    private array $commands = [];

    public function command(string $name, CliCommand $cliCommand): void
    {
        $this->commands[$name] = $cliCommand;
    }

    /**
     * @param list<string> $inputArgs
     */
    public function run(array $inputArgs): int
    {
        if (defined('STDIN') === false) {
            define('STDIN', fopen('php://stdin', 'r'));
        }

        if (defined('STDOUT') === false) {
            define('STDOUT', fopen('php://stdout', 'w'));
        }

        (new Application())->bootstrap();
        (new ApplicationState())->throw(null);

        if (defined('SCENARIO_CLI_DISABLED') === false) {
            define('SCENARIO_CLI_DISABLED', false);
        }

        $input = new Input($inputArgs);

        if ($input->force() !== true
            && SCENARIO_CLI_DISABLED === true) {
            echo 'CLI DISABLED!' . PHP_EOL;
            return Command::Error->value;
        }

        $this->command('apply', new ApplyScenarioCommand());
        $this->command('debug', new DebugScenarioCommand(new ScenarioTestFinder()));
        $this->command('install', new InstallScenarioCommand());
        $this->command('list', new ListScenariosCommand());
        $this->command('make', new MakeScenarioCommand());
        $this->command('parameter', new ListParameterTypesCommand());
        $this->command('refresh', new RefreshDatabaseCommand());

        $config = Application::config();
        if ($config === null) {
            return Command::Error->value;
        }

        foreach ($config->getExtensions() as $extension) {
            if (is_subclass_of($extension, Extension::class) === false) {
                throw new InvalidExtensionSubClassException(get_class($extension));
            }

            $cliApplication = $extension->cli();
            if ($cliApplication !== null) {
                $cliApplication->boot($this);
            }
        }

        $output = new Output(new AnsiStyler(new SystemTerminal()), new NativeTerminalIO());

        if ($input->command() !== null
            && isset($this->commands[$input->command()]) === true) {
            return $this->commands[$input->command()]
                ->run(
                    $input,
                    $output,
                )->value;
        }

        return (new ListCommands($this->commands))
            ->run(
                $input,
                $output,
            )->value;
    }
}
