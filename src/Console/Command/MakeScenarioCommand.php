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

use Stateforge\Scenario\Core\Console\Input\Argument;
use Stateforge\Scenario\Core\Console\Input\InputType;
use Stateforge\Scenario\Core\Contract\CliInput;
use Stateforge\Scenario\Core\Contract\CliOutput;
use Stateforge\Scenario\Core\Runtime\Application;
use function array_keys;
use function array_map;
use function count;
use function explode;
use function file_get_contents;
use function file_put_contents;
use function implode;
use function in_array;
use function is_file;
use function preg_match;
use function str_replace;
use function ucfirst;
use const DIRECTORY_SEPARATOR;

final class MakeScenarioCommand extends CliCommand
{
    public function description(): string
    {
        return 'Make a scenario or config file.';
    }

    protected function define(CliInput $input): void
    {
        $input->defineArgument(new Argument('type', InputType::String));
    }

    protected function execute(CliInput $input, CliOutput $output): Command
    {
        $type = $input->argument('type') ?? '';
        $options = ['scenario', 'config'];
        if (in_array($type, $options, true) === false) {
            $type = $options[
                $output->choice('Please select the type do would like to make.', $options, '0')
            ];
        }

        return match ($type) {
            'scenario' => $this->scenario($output),
            'config' => $this->config($output),
        };
    }

    private function scenario(CliOutput $output): Command
    {
        $file = $this->getBlueprint('scenario');

        if (is_file($file) === false) {
            $output->error('Scenario generation failed.');
            return Command::Error;
        }

        $suites = Application::config()?->getSuites() ?? [];
        $options = array_keys($suites);
        $suite = $suites[$options[0]];
        if (count($suites) > 1) {
            $suite = $suites[
                $options[
                (int)$output->choice('Please select the suite where you want to make a scenario.', $options)
                ]
            ];
        }

        $name = $output->ask(
            'Please insert a class name for the new scenario',
            null,
            function (string $name): bool {
                return preg_match('/^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$/', $name) === 1;
            },
        );

        if ($name === null) {
            $output->error('Invalid Scenario name.');
            return Command::Error;
        }

        $scenario = Application::getRootDir() . DIRECTORY_SEPARATOR . $suite->directory . DIRECTORY_SEPARATOR . ucfirst($name) . '.php';
        if (is_file($scenario) === true) {
            $output->error('Scenario already exists.');
            return Command::Error;
        }

        file_put_contents(
            $scenario,
            str_replace(
                [
                    '%nameSpace%',
                    '%className%',
                ],
                [
                    implode('\\', array_map(function ($part) {
                        return ucfirst($part);
                    }, explode(DIRECTORY_SEPARATOR, $suite->directory))),
                    ucfirst($name),
                ],
                (string)file_get_contents($file),
            ),
        );

        if (is_file($scenario) === false) {
            $output->error('Scenario generation failed.');
            return Command::Error;
        }

        $output->success('Scenario "' . $scenario . '" generated, please modify to your needs.');
        return Command::Success;
    }

    private function config(CliOutput $output): Command
    {
        $file = $this->getBlueprint('config');

        if (is_file($file) === false) {
            $output->error('Config file generation failed.');
            return Command::Error;
        }

        $configFile = Application::getRootDir() . DIRECTORY_SEPARATOR . 'scenario.dist.xml';
        file_put_contents($configFile, file_get_contents($file));

        if (is_file($configFile) === false) {
            $output->error('Config file generation failed.');
            return Command::Error;
        }

        $output->success('Config file generated, please modify to your needs.');
        return Command::Success;
    }

    private function getBlueprint(string $name): string
    {
        return Application::getRootDir() . DIRECTORY_SEPARATOR .
            'vendor' . DIRECTORY_SEPARATOR .
            'stateforge' . DIRECTORY_SEPARATOR .
            'scenario-core' . DIRECTORY_SEPARATOR .
            'blueprint' . DIRECTORY_SEPARATOR .
            $name . '.blueprint';
    }
}
