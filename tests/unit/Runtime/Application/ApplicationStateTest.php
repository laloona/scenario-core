<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Tests\Unit\Runtime\Application;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Scenario\Core\Runtime\Application\ApplicationState;
use Scenario\Core\Runtime\Exception\ApplicationFailureException;
use Scenario\Core\Tests\Unit\ApplicationStateMock;

#[CoversClass(ApplicationState::class)]
#[UsesClass(ApplicationFailureException::class)]
#[Group('runtime')]
#[Small]
final class ApplicationStateTest extends TestCase
{
    use ApplicationStateMock;

    protected function tearDown(): void
    {
        $this->resetApplicationState();
    }

    public function testInitialStateIsSuccessAndThrowDoesNothingWhenNoFailure(): void
    {
        $state = new ApplicationState();

        self::assertTrue($state->isSuccess());
        self::assertFalse($state->isFailed());
        self::assertNull($state->failure(null));

        $state->throw(null);
    }

    public function testWhenStateIsSuccessWithRegisteredClassAndThrowDoesNothingWhenNoFailure(): void
    {
        $state = new ApplicationState();
        $state->addClass('SomeClass');

        self::assertTrue($state->isSuccess());
        self::assertFalse($state->isFailed());
        self::assertNull($state->failure('SomeClass'));

        $state->throw('SomeClass');
    }

    public function testFailMarksStateAsFailed(): void
    {
        $state = new ApplicationState();
        $state->fail(new RuntimeException('some error happened'));

        self::assertTrue($state->isFailed());
        self::assertFalse($state->isSuccess());
        self::assertInstanceOf(ApplicationFailureException::class, $state->failure(null));
    }

    public function testFailureReturnsNullIfClassNotRegisteredAndThrowDoesNothingWhenClassIsNotRegistered(): void
    {
        $state = new ApplicationState();
        $state->fail(new RuntimeException('some error happened'));

        self::assertNull($state->failure('UnknownClass'));

        $state->throw('UnknownClass');
    }

    public function testFailureReturnsExceptionIfClassWasRegisteredAndThrowWhenFailureMatchesClass(): void
    {
        $state = new ApplicationState();
        $state->addClass('SomeClass');
        $state->fail(new RuntimeException('some error happened'));

        self::assertInstanceOf(ApplicationFailureException::class, $state->failure('SomeClass'));

        $this->expectException(ApplicationFailureException::class);
        $state->throw('SomeClass');
    }

    public function testFailureThrowWhenNoClassIsGiven(): void
    {
        $state = new ApplicationState();
        $state->fail(new RuntimeException('some error happened'));

        self::assertNull($state->failure('UnknownClass'));

        $this->expectException(ApplicationFailureException::class);
        $state->throw(null);
    }

    public function testFailuresAreTrackedPerClass(): void
    {
        $state = new ApplicationState();
        $state->addClass('ClassA');
        $state->addClass('ClassB');

        $state->fail(new RuntimeException('A'));

        self::assertInstanceOf(ApplicationFailureException::class, $state->failure('ClassA'));
        self::assertInstanceOf(ApplicationFailureException::class, $state->failure('ClassB'));
        self::assertNull($state->failure('ClassC'));
    }

    public function testFailureExceptionWrapsOriginalThrowable(): void
    {
        $state = new ApplicationState();
        $throwable = new RuntimeException('some error happened', 123);

        $state->fail($throwable);

        $failure = $state->failure(null);

        self::assertInstanceOf(ApplicationFailureException::class, $failure);
        self::assertSame($throwable, $failure->getPrevious());
        self::assertStringContainsString('Scenario application failure', $failure->getMessage());
    }
}
