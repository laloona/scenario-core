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
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;
use Scenario\Core\Runtime\Application\ApplicationState;
use Scenario\Core\Runtime\Exception\ApplicationFailureException;

#[CoversClass(ApplicationState::class)]
#[UsesClass(ApplicationFailureException::class)]
#[Group('runtime')]
final class ApplicationStateTest extends TestCase
{
    protected function tearDown(): void
    {
        $applicationState = new ReflectionClass(ApplicationState::class);
        $throwable = $applicationState->getProperty('throwable');
        $throwable->setValue(null, null);
        $classes = $applicationState->getProperty('classes');
        $classes->setValue(null, []);
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
}
