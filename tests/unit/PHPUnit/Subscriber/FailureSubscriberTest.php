<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Tests\Unit\PHPUnit\Subscriber;

use PHPUnit\Event\Code\TestDox;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Telemetry\Duration;
use PHPUnit\Event\Telemetry\GarbageCollectorStatus;
use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Event\Telemetry\Info;
use PHPUnit\Event\Telemetry\MemoryUsage;
use PHPUnit\Event\Telemetry\Snapshot;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\TestData\TestDataCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\MetadataCollection;
use RuntimeException;
use Stateforge\Scenario\Core\PHPUnit\ErrorEmitter;
use Stateforge\Scenario\Core\PHPUnit\Subscriber\FailureSubscriber;
use Stateforge\Scenario\Core\Runtime\Application\ApplicationState;
use Stateforge\Scenario\Core\Runtime\Application\TestClassState;
use Stateforge\Scenario\Core\Runtime\Application\TestMethodState;
use Stateforge\Scenario\Core\Runtime\Exception\Application\ApplicationFailureException;
use Stateforge\Scenario\Core\Runtime\Exception\Application\TestClassFailureException;
use Stateforge\Scenario\Core\Runtime\Exception\Application\TestMethodFailureException;
use Stateforge\Scenario\Core\Tests\Files\AnotherScenario;
use Stateforge\Scenario\Core\Tests\Files\ValidScenario;
use Stateforge\Scenario\Core\Tests\Unit\ApplicationStateMock;
use Stateforge\Scenario\Core\Tests\Unit\TestClassStateMock;
use Stateforge\Scenario\Core\Tests\Unit\TestMethodStateMock;

#[CoversClass(FailureSubscriber::class)]
#[UsesClass(ApplicationState::class)]
#[UsesClass(TestClassState::class)]
#[UsesClass(TestMethodState::class)]
#[UsesClass(ApplicationFailureException::class)]
#[UsesClass(TestClassFailureException::class)]
#[UsesClass(TestMethodFailureException::class)]
#[Group('phpunit')]
#[Small]
final class FailureSubscriberTest extends TestCase
{
    use ApplicationStateMock;
    use TestClassStateMock;
    use TestMethodStateMock;

    protected function setUp(): void
    {
        $this->resetApplicationState();
        $this->resetClassMethodState();
        $this->resetTestMethodState();
    }

    protected function tearDown(): void
    {
        $this->resetApplicationState();
        $this->resetClassMethodState();
        $this->resetTestMethodState();
    }

    public function testNotifyThrowsApplicationFailureAndEmitsIt(): void
    {
        $event = $this->finishedEvent(ValidScenario::class, 'myExample');

        $testMethod = $event->test();
        self::assertInstanceOf(TestMethod::class, $testMethod);

        /* @var class-string $className */
        $className = $testMethod->className();

        $applicationState = new ApplicationState();
        $applicationState->addClass($className);
        $applicationState->fail(new RuntimeException('application failed'));

        $errorEmitter = $this->createMock(ErrorEmitter::class);
        $errorEmitter->expects(self::once())
            ->method('testErrored')
            ->with(
                self::identicalTo($testMethod),
                self::isInstanceOf(ApplicationFailureException::class),
            );

        $this->expectException(ApplicationFailureException::class);

        (new FailureSubscriber($errorEmitter))->notify($event);
    }

    public function testNotifyThrowsClassFailureWhenApplicationFailureDoesNotMatchClass(): void
    {
        $event = $this->finishedEvent(ValidScenario::class, 'myExample');

        $testMethod = $event->test();
        self::assertInstanceOf(TestMethod::class, $testMethod);

        $applicationState = new ApplicationState();
        $applicationState->addClass(AnotherScenario::class);
        $applicationState->fail(new RuntimeException('application failed'));

        (new TestClassState())->fail($testMethod->className(), new RuntimeException('class failed'));

        $errorEmitter = $this->createMock(ErrorEmitter::class);
        $errorEmitter->expects(self::once())
            ->method('testErrored')
            ->with(
                self::identicalTo($event->test()),
                self::isInstanceOf(TestClassFailureException::class),
            );

        $this->expectException(TestClassFailureException::class);

        (new FailureSubscriber($errorEmitter))->notify($event);
    }

    public function testNotifyThrowsMethodFailureWhenOnlyMethodStateFailed(): void
    {
        $event = $this->finishedEvent(ValidScenario::class, 'myExample');

        $testMethod = $event->test();
        self::assertInstanceOf(TestMethod::class, $testMethod);

        (new TestMethodState())->fail($testMethod->className(), $testMethod->methodName(), new RuntimeException('method failed'));

        $errorEmitter = $this->createMock(ErrorEmitter::class);
        $errorEmitter->expects(self::once())
            ->method('testErrored')
            ->with(
                self::identicalTo($testMethod),
                self::isInstanceOf(TestMethodFailureException::class),
            );

        $this->expectException(TestMethodFailureException::class);

        (new FailureSubscriber($errorEmitter))->notify($event);
    }

    /**
     * @param class-string $className
     * @param non-empty-string $methodName
     */
    private function finishedEvent(string $className, string $methodName): Finished
    {
        return new Finished(
            new Info(
                new Snapshot(
                    HRTime::fromSecondsAndNanoseconds(1, 0),
                    MemoryUsage::fromBytes(1),
                    MemoryUsage::fromBytes(1),
                    new GarbageCollectorStatus(0, 0, 0, 0, null, null, null, null, null, null, null, null),
                ),
                Duration::fromSecondsAndNanoseconds(0, 0),
                MemoryUsage::fromBytes(0),
                Duration::fromSecondsAndNanoseconds(0, 0),
                MemoryUsage::fromBytes(0),
            ),
            new TestMethod(
                $className,
                $methodName,
                __FILE__,
                1,
                new TestDox($className, $methodName, $methodName),
                MetadataCollection::fromArray([]),
                TestDataCollection::fromArray([]),
            ),
            0,
        );
    }
}
