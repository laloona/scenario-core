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

use Stateforge\Scenario\Core\Console\Exception\InputException;
use Stateforge\Scenario\Core\Console\Input\InputType;
use Stateforge\Scenario\Core\Console\Input\Option;
use Stateforge\Scenario\Core\Contract\CliInput;
use Stateforge\Scenario\Core\Contract\CliOutput;
use Stateforge\Scenario\Core\Runtime\Application\ApplicationState;
use Throwable;
use const PHP_EOL;

abstract class CliCommand
{
    final public function run(CliInput $input, CliOutput $output): Command
    {
        try {
            (new ApplicationState())->throw(null);

            $input->defineOption(new Option('quiet', InputType::Boolean));
            $this->define($input);
            $input->resolve();

            if ($input->option('quiet') === true) {
                return $this->execute($input, $output);
            }

            $output->warn('Plaese don\'t use these commands on production systems as data will be modified.');

            return $output->confirm('Do you want to continue?', false) === true
                ? $this->execute($input, $output)
                : Command::Error;
        } catch (InputException $exception) {
            $output->error($exception->getMessage());
        } catch (Throwable $throwable) {
            $output->error('Exception was thrown: ' .  $throwable->getMessage() . PHP_EOL . PHP_EOL. 'Trace:' . PHP_EOL . $throwable->getTraceAsString());
        }

        return Command::Error;
    }

    protected function define(CliInput $input): void
    {
    }

    abstract protected function execute(CliInput $input, CliOutput $output): Command;

    abstract public function description(): string;
}
