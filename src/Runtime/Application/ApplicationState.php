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

use Scenario\Core\Runtime\Exception\Application\ApplicationFailureException;
use Throwable;
use function array_search;

final class ApplicationState
{
    private static ?Throwable $throwable = null;

    /**
     * @var list<string>
     */
    private static array $classes = [];

    public function addClass(string $class): void
    {
        self::$classes[] = $class;
    }

    public function failure(?string $class): ?ApplicationFailureException
    {
        if ($this->isSuccess() === true
            || ($class !== null
                && array_search($class, self::$classes, true) === false)) {
            return null;
        }

        return self::$throwable === null
            ? null
            : new ApplicationFailureException(self::$throwable);
    }

    public function fail(Throwable $throwable): void
    {
        self::$throwable = $throwable;
    }

    public function isSuccess(): bool
    {
        return $this->isFailed() === false;
    }

    public function isFailed(): bool
    {
        return self::$throwable !== null;
    }

    public function throw(?string $class): void
    {
        $failure = $this->failure($class);
        if ($failure !== null) {
            throw $failure;
        }
    }
}
