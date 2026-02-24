<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\PHPUnit\Subscriber;

use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Code\ThrowableBuilder;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\FinishedSubscriber;
use Scenario\Core\Runtime\Application\ApplicationState;
use Scenario\Core\Runtime\Application\TestClassState;
use Scenario\Core\Runtime\Application\TestMethodState;
use Throwable;

final class FailureSubscriber implements FinishedSubscriber
{
    public function notify(Finished $event): void
    {
        if ($event->test()->isTestMethod() === true) {
            $this->state($event->test(), new ApplicationState()->failure($event->test()->className()));
            $this->state($event->test(), new TestClassState()->failure($event->test()->className()));
            $this->state($event->test(), new TestMethodState()->failure($event->test()->className(), $event->test()));
        }
    }

    private function state(TestMethod $test, ?Throwable $throwable): void
    {
        if ($throwable !== null) {
            Facade::emitter()->testErrored($test, ThrowableBuilder::from($throwable));

            throw $throwable;
        }
    }
}
