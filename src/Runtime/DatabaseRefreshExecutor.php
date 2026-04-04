<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Runtime;

use Stateforge\Scenario\Core\Attribute\RefreshDatabase;
use Stateforge\Scenario\Core\Contract\DatabaseRefreshExecutorInterface;
use Stateforge\Scenario\Core\Runtime\Exception\Metadata\ConnectionException;
use function is_file;
use const DIRECTORY_SEPARATOR;

final class DatabaseRefreshExecutor implements DatabaseRefreshExecutorInterface
{
    public function execute(RefreshDatabase $metaData): void
    {
        $connections = Application::config()?->getConnections() ?? [];
        if (isset($connections[$metaData->connection ?? '']) === true
            && is_file(Application::getRootDir() . DIRECTORY_SEPARATOR . $connections[$metaData->connection ?? '']->config) === true) {
            include(Application::getRootDir() . DIRECTORY_SEPARATOR . $connections[$metaData->connection ?? '']->config);
            return;
        }

        throw new ConnectionException($metaData->connection ?? '');
    }
}
