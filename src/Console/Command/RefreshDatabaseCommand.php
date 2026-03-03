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

    protected function execute(CliInput $input, CliOutput $output): Command
    {
        HandlerRegistry::getInstance()
            ->attributeHandler(RefreshDatabase::class)
            ->handle(
                new AttributeContext(
                    __CLASS__,
                    __METHOD__,
                    ExecutionType::Up,
                    false,
                ),
                new RefreshDatabase((string)$input->option('connection')),
            );

        new TestMethodState()->throw(__CLASS__, __METHOD__);

        $output->success('Refresh executed.');
        return Command::Success;
    }
}
