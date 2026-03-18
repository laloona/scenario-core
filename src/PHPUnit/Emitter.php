<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\PHPUnit;

use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Code\ThrowableBuilder;
use PHPUnit\Event\Facade;
use Throwable;

final class Emitter implements ErrorEmitter
{
    public function testErrored(TestMethod $test, Throwable $throwable): void
    {
        Facade::emitter()->testErrored($test, ThrowableBuilder::from($throwable));
    }
}
