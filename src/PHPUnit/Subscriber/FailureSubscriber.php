<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\PHPUnit\Subscriber;

use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\FinishedSubscriber;
use Stateforge\Scenario\Core\PHPUnit\ErrorEmitter;
use Stateforge\Scenario\Core\Runtime\Application\ApplicationState;
use Stateforge\Scenario\Core\Runtime\Application\TestClassState;
use Stateforge\Scenario\Core\Runtime\Application\TestMethodState;
use Throwable;

final class FailureSubscriber implements FinishedSubscriber
{
    public function __construct(private ErrorEmitter $errorEmitter)
    {
    }

    public function notify(Finished $event): void
    {
        if ($event->test()->isTestMethod() === true) {
            /* @var TestMethod $testMethod */
            $testMethod = $event->test();

            $this->throwOnError(
                $testMethod,
                (new ApplicationState())->failure($event->test()->className()),
            );
            $this->throwOnError(
                $testMethod,
                (new TestClassState())->failure($event->test()->className()),
            );
            $this->throwOnError(
                $testMethod,
                (new TestMethodState())->failure($event->test()->className(), $testMethod),
            );
        }
    }

    private function throwOnError(TestMethod $test, ?Throwable $throwable): void
    {
        if ($throwable !== null) {
            $this->errorEmitter->testErrored($test, $throwable);
            throw $throwable;
        }
    }
}
