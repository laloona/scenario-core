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

use Scenario\Core\Runtime\Application\Configuration\Value\ConnectionValue;
use Scenario\Core\Runtime\Application\Configuration\Value\SuiteValue;

interface Configuration
{
    public function getBootstrap(): string;

    public function getCacheDirectory(): string;

    public function getCacheKey(): ?string;

    public function setCacheKey(string $cacheKey): void;

    /**
     * @return SuiteValue[]
     */
    public function getSuites(): array;

    /**
     * @return ConnectionValue[]
     */
    public function getConnections(): array;
}
