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

use Scenario\Core\Application;
use Scenario\Core\Attribute\RefreshDatabase;
use Scenario\Core\Runtime\Exception\ConnectionException;
use Scenario\Core\Runtime\Metadata\AttributeContext;
use Scenario\Core\Runtime\Metadata\ExecutionType;

final class RefreshDatabaseHandler extends AttributeHandler
{
    protected function attributeName(): string
    {
        return RefreshDatabase::class;
    }

    protected function execute(AttributeContext $context, object $metaData): void
    {
        /** @var RefreshDatabase $metaData */
        if ($context->executionType === ExecutionType::Up) {
            $connections = Application::config()?->getConnections() ?? [];
            if (isset($connections[$metaData->connection]) === true
                && is_file(Application::getRootDir() . DIRECTORY_SEPARATOR . $connections[$metaData->connection]->config) === true) {
                include(Application::getRootDir() . DIRECTORY_SEPARATOR . $connections[$metaData->connection]->config);
                return;
            }

            throw new ConnectionException($metaData->connection ?? '');
        }
    }
}
