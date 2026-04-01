<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Runtime\Metadata\Handler;

use Scenario\Core\Attribute\RefreshDatabase;
use Scenario\Core\Contract\DatabaseRefreshExecutorInterface;
use Scenario\Core\Runtime\Metadata\AttributeContext;
use Scenario\Core\Runtime\Metadata\ExecutionType;

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
            $context->audit(__CLASS__, [ 'connection' => $metaData->connection ]);

            if ($context->dryRun === true) {
                return;
            }

            $this->refreshExecutor->execute($metaData);
        }
    }
}
