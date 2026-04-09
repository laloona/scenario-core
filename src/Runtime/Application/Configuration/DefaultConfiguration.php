<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Runtime\Application\Configuration;

use Stateforge\Scenario\Core\Attribute\ApplyScenario;
use Stateforge\Scenario\Core\Attribute\RefreshDatabase;
use Stateforge\Scenario\Core\Runtime\Application;
use Stateforge\Scenario\Core\Runtime\Application\Configuration\Value\ConnectionValue;
use Stateforge\Scenario\Core\Runtime\Application\Configuration\Value\SuiteValue;
use function md5;
use function microtime;
use const DIRECTORY_SEPARATOR;

final class DefaultConfiguration implements Configuration
{
    public function getBootstrap(): string
    {
        return '';
    }

    public function getCacheDirectory(): string
    {
        return Application::getRootDir() . DIRECTORY_SEPARATOR .'.scenario.cache';
    }

    public function getCacheKey(): string
    {
        return md5((string)microtime(true));
    }

    public function setCacheKey(string $cacheKey): void
    {
    }

    public function getParameterDirectory(): string
    {
        return 'parameter' . DIRECTORY_SEPARATOR;
    }

    /**
     * @return array<string, SuiteValue>
     */
    public function getSuites(): array
    {
        return [
            'main' => new SuiteValue('main', Application::getRootDir() . DIRECTORY_SEPARATOR .'scenario'),
        ];
    }

    /**
     * @return array<string, ConnectionValue>
     */
    public function getConnections(): array
    {
        return [];
    }

    /**
     * @return list<class-string>
     */
    public function getAttributes(): array
    {
        return [
            ApplyScenario::class,
            RefreshDatabase::class,
        ];
    }
}
