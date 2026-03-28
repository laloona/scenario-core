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
use Scenario\Core\PHPUnit\Configuration\ConfigFinder;
use Scenario\Core\PHPUnit\Configuration\Configurator;
use Scenario\Core\PHPUnit\Configuration\Configured;

final class InstallScenarioCommand extends CliCommand
{
    public function description(): string
    {
        return 'Configure the extension for PHPUnit.';
    }

    protected function execute(CliInput $input, CliOutput $output): Command
    {
        $finder = new ConfigFinder();
        $check = new Configured($finder);
        if ($check->isConfigured() === true) {
            if ($input->option('quiet') !== true) {
                $output->error('The PHPUnit extension is already configured.');
            }
            return Command::Error;
        }

        if ($input->option('quiet') === true
            || $output->confirm('The installation adds the scenario extension to the PHPUnit config. Do you want to continue?', false) === true) {
            (new Configurator($finder, $check))->configure();

            if ($check->isConfigured() === true) {
                if ($input->option('quiet') !== true) {
                    $output->success('Scenario extension is configured for PHPUnit.');
                }
                return Command::Success;
            }
        }

        if ($input->option('quiet') !== true) {
            $output->error('Configuring PHPUnit failed.');
        }
        return Command::Error;
    }
}
