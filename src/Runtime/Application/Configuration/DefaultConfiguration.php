<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Runtime\Application\Configuration;

use Scenario\Core\Attribute\ApplyScenario;
use Scenario\Core\Attribute\RefreshDatabase;
use Scenario\Core\Runtime\Application;
use Scenario\Core\Runtime\Application\Configuration\Value\ConnectionValue;
use Scenario\Core\Runtime\Application\Configuration\Value\SuiteValue;

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
