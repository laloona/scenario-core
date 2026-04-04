<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Runtime\Metadata\Handler;

use Stateforge\Scenario\Core\Attribute\RefreshDatabase;
use Stateforge\Scenario\Core\Contract\DatabaseRefreshExecutorInterface;
use Stateforge\Scenario\Core\Runtime\Metadata\AttributeContext;
use Stateforge\Scenario\Core\Runtime\Metadata\ExecutionType;
use function get_class;

final class RefreshDatabaseHandler extends AttributeHandler
{
    public function __construct(private DatabaseRefreshExecutorInterface $refreshExecutor)
    {
    }

    protected function attributeName(): string
    {
        return RefreshDatabase::class;
    }

    protected function execute(AttributeContext $context, object $metaData): void
    {
        /** @var RefreshDatabase $metaData */
        if ($context->executionType === ExecutionType::Up) {
            $context->audit(get_class($this->refreshExecutor), [ 'connection' => $metaData->connection ]);

            if ($context->dryRun === true) {
                return;
            }

            $this->refreshExecutor->execute($metaData);
        }
    }
}
