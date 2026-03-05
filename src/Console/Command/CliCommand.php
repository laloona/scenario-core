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

use Scenario\Core\Console\Output;
use Scenario\Core\Console\Output\Theme\AnsiStyler;
use Scenario\Core\Contract\CliInput;
use Scenario\Core\Contract\CliOutput;
use Scenario\Core\Runtime\Application\ApplicationState;
use Throwable;

abstract class CliCommand
{
    final public function run(CliInput $input): Command
    {
        $output = new Output(new AnsiStyler());

        try {
            (new ApplicationState())->throw(null);

            if ($input->option('quiet') === true) {
                return $this->execute($input, $output);
            }

            $output->warn('Plaese don\'t use these commands on production systems as data will be modified.');

            return $output->confirm('Do you want to continue?', false) === true
                ? $this->execute($input, $output)
                : Command::Error;
        } catch (Throwable $throwable) {
            $output->error('Exception was thrown: ' .  $throwable->getMessage() . PHP_EOL . PHP_EOL. 'Trace:' . PHP_EOL . $throwable->getTraceAsString());
            return Command::Error;
        }
    }

    abstract protected function execute(CliInput $input, CliOutput $output): Command;

    abstract public function description(): string;
}
