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

use Stateforge\Scenario\Core\Runtime\Application\Configuration\Value\ConnectionValue;
use Stateforge\Scenario\Core\Runtime\Application\Configuration\Value\SuiteValue;
use function count;

final class LoadedConfiguration implements Configuration
{
    private ?string $bootstrap = null;

    private ?string $cacheDirectory = null;

    private ?string $cacheKey = null;

    private ?string $parameterDirectory = null;

    /**
     * @var list<string>
     */
    private array $parameterDirectories = [];

    /**
     * @var array<string, ConnectionValue>
     */
    private array $connections = [];

    /**
     * @var array<string, SuiteValue>
     */
    private array $suites = [];

    public function __construct(private DefaultConfiguration $defaultConfiguration)
    {
    }

    public function getBootstrap(): string
    {
        return $this->bootstrap ?? $this->defaultConfiguration->getBootstrap();
    }

    public function setBootstrap(string $bootstrap): void
    {
        $this->bootstrap = $bootstrap;
    }

    public function getCacheDirectory(): string
    {
        return $this->cacheDirectory ?? $this->defaultConfiguration->getCacheDirectory();
    }

    public function setCacheDirectory(string $cacheDirectory): void
    {
        $this->cacheDirectory = $cacheDirectory;
    }

    public function getCacheKey(): string
    {
        return $this->cacheKey ?? $this->defaultConfiguration->getCacheKey();
    }

    public function setCacheKey(string $cacheKey): void
    {
        $this->cacheKey = $cacheKey;
    }

    public function setParameterDirectory(string $parameterDirectory): void
    {
        $this->parameterDirectory = $parameterDirectory;
        $this->addParameterDirectory($parameterDirectory);
    }

    public function getParameterDirectory(): string
    {
        return $this->parameterDirectory ?? $this->defaultConfiguration->getParameterDirectory();
    }

    /**
     * @return list<string>
     */
    public function getParameterDirectories(): array
    {
        return count($this->parameterDirectories) === 0
            ? $this->defaultConfiguration->getParameterDirectories()
            : $this->parameterDirectories;
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
        return count($this->suites) === 0
            ? $this->defaultConfiguration->getSuites()
            : $this->suites;
    }

    /**
     * @param array<string, SuiteValue> $suites
     */
    public function setSuites(array $suites): void
    {
        $this->suites = $suites;
    }

    /**
     * @return array<string, ConnectionValue>
     */
    public function getConnections(): array
    {
        return count($this->connections) === 0
            ? $this->defaultConfiguration->getConnections()
            : $this->connections;
    }

    /**
     * @param array<string, ConnectionValue> $connections
     */
    public function setConnections(array $connections): void
    {
        $this->connections = $connections;
    }

    /**
     * @return list<class-string>
     */
    public function getAttributes(): array
    {
        return $this->defaultConfiguration->getAttributes();
    }
}
