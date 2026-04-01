<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Runtime;

use Scenario\Core\Attribute\RefreshDatabase;
use Scenario\Core\Contract\DatabaseRefreshExecutorInterface;
use Scenario\Core\Runtime\Exception\Metadata\ConnectionException;
use function is_file;
use const DIRECTORY_SEPARATOR;

final class DatabaseRefreshExecutor implements DatabaseRefreshExecutorInterface
{
    public function execute(RefreshDatabase $metaData): void
    {
        $connections = Application::config()?->getConnections() ?? [];
        if (isset($connections[$metaData->connection]) === true
            && is_file(Application::getRootDir() . DIRECTORY_SEPARATOR . $connections[$metaData->connection]->config) === true) {
            include(Application::getRootDir() . DIRECTORY_SEPARATOR . $connections[$metaData->connection]->config);
            return;
        }

        throw new ConnectionException($metaData->connection ?? '');
    }
}
