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

use Scenario\Core\Attribute\RefreshDatabase;
use Scenario\Core\Console\Input\InputType;
use Scenario\Core\Console\Input\Option;
use Scenario\Core\Contract\CliInput;
use Scenario\Core\Contract\CliOutput;
use Scenario\Core\Runtime\Application\TestMethodState;
use Scenario\Core\Runtime\Metadata\AttributeContext;
use Scenario\Core\Runtime\Metadata\ExecutionType;
use Scenario\Core\Runtime\Metadata\HandlerRegistry;

final class RefreshDatabaseCommand extends CliCommand
{
    public function description(): string
    {
        return 'Executes the database refresh. Use --connection="connection_name" to specify given connection.';
    }

    protected function define(CliInput $input): void
    {
        $input->defineOption(new Option('connection', InputType::String));
    }

    protected function execute(CliInput $input, CliOutput $output): Command
    {
        $connection = null;
        if (is_string($input->option('connection')) === true) {
            $connection = $input->option('connection');
        }

        HandlerRegistry::getInstance()
            ->attributeHandler(RefreshDatabase::class)
            ->handle(
                AttributeContext::getInstance(
                    __CLASS__,
                    __METHOD__,
                    ExecutionType::Up,
                    false,
                    null,
                ),
                new RefreshDatabase($connection),
            );

        (new TestMethodState())->failure(__CLASS__, __METHOD__);

        $output->success('Refresh executed.');
        return Command::Success;
    }
}
