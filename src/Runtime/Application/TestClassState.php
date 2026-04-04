<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Runtime\Application;

use Stateforge\Scenario\Core\Runtime\Exception\Application\TestClassFailureException;
use Throwable;

final class TestClassState
{
    /**
     * @var array<string, Throwable>
     */
    private static array $throwables = [];

    public function failure(string $class): ?TestClassFailureException
    {
        if ($this->isSuccess($class) === true) {
            return null;
        }

        return new TestClassFailureException($class, self::$throwables[$class]);
    }

    public function fail(string $class, Throwable $throwable): void
    {
        self::$throwables[$class] = $throwable;
    }

    public function isSuccess(string $class): bool
    {
        return $this->isFailed($class) === false;
    }

    public function isFailed(string $class): bool
    {
        return (self::$throwables[$class] ?? null) !== null;
    }

    public function throw(string $class): void
    {
        $failure = $this->failure($class);
        if ($failure !== null) {
            throw $failure;
        }
    }
}
