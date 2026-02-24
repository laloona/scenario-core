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

final class ListCommands extends CliCommand
{
    /**
     * @param array<string, CliCommand> $commands
     */
    public function __construct(
        private array $commands,
    ) {
    }

    public function description(): string
    {
        return 'List all available commands';
    }

    protected function execute(CliInput $input, CliOutput $output): Command
    {
        $output->writeln('');

        if ($input->command() !== null) {
            $output->error(
                sprintf(
                    'The command "%s" is unknown.',
                    $input->command(),
                ),
            );
            $output->writeln('');
        }

        $list = [];
        foreach ($this->commands as $name => $command) {
            $list[] = [$name, $command->description()];
        }

        $output->headline('available commands');
        $output->table(null, $list, null, false);

        return Command::Success;
    }
}
