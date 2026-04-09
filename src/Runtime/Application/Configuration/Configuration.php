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

interface Configuration
{
    public function getBootstrap(): string;

    public function getCacheDirectory(): string;

    public function getCacheKey(): ?string;

    public function setCacheKey(string $cacheKey): void;

    public function getParameterDirectory(): string;

    /**
     * @return array<string, SuiteValue>
     */
    public function getSuites(): array;

    /**
     * @return array<string, ConnectionValue>
     */
    public function getConnections(): array;

    /**
     * @return list<class-string>
     */
    public function getAttributes(): array;
}
