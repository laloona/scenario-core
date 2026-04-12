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
use Stateforge\Scenario\Core\Runtime\Application\Configuration\Value\ConnectionValue;
use Stateforge\Scenario\Core\Runtime\Application\Configuration\Value\SuiteValue;
use function md5;
use function microtime;
use const DIRECTORY_SEPARATOR;

final class DefaultConfiguration implements Configuration
{
    /**
     * @var list<string>
     */
    private array $parameterDirectories = [];

    public function getBootstrap(): string
    {
        return '';
    }

    public function getCacheDirectory(): string
    {
        return '.scenario.cache';
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
        return 'scenario' . DIRECTORY_SEPARATOR . 'parameter';
    }

    /**
     * @return list<string>
     */
    public function getParameterDirectories(): array
    {
        return [ $this->getParameterDirectory(), ...$this->parameterDirectories ];
    }

    public function addParameterDirectory(string $parameterDirectory): void
    {
        $this->parameterDirectories[] = $parameterDirectory;
    }

    /**
     * @return array<string, SuiteValue>
     */
    public function getSuites(): array
    {
        return [
            'main' => new SuiteValue('main', 'scenario' . DIRECTORY_SEPARATOR . 'main'),
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
