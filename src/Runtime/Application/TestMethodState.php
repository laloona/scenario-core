<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Runtime\Application;

use PHPUnit\Event\Code\TestMethod;
use Scenario\Core\Runtime\Exception\Application\TestMethodFailureException;
use Throwable;

final class TestMethodState
{
    /**
     * @var array<string, Throwable >
     */
    private static array $throwables = [];

    private function key(string $class, string $method): string
    {
        return $class . '::' . $method;
    }

    public function failure(string $class, TestMethod|string $method): ?TestMethodFailureException
    {
        $methodName = $method instanceof TestMethod ? $method->methodName() : $method;
        if ($this->isSuccess($class, $methodName) === true) {
            return null;
        }

        $failure = new TestMethodFailureException($this->key($class, $methodName), self::$throwables[$this->key($class, $methodName)]);
        unset(self::$throwables[$this->key($class, $methodName)]);
        return $failure;
    }

    public function fail(string $class, string $method, Throwable $throwable): void
    {
        self::$throwables[$this->key($class, $method)] = $throwable;
    }

    public function isSuccess(string $class, string $method): bool
    {
        return $this->isFailed($class, $method) === false;
    }

    public function isFailed(string $class, string $method): bool
    {
        return (self::$throwables[$this->key($class, $method)] ?? null) !== null;
    }

    public function throw(string $class, string $method): void
    {
        $failure = $this->failure($class, $method);
        if ($failure !== null) {
            throw $failure;
        }
    }
}
