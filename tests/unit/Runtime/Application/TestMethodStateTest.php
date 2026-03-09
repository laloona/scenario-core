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
use Scenario\Core\Runtime\Application\TestMethodState;
use Scenario\Core\Runtime\Exception\TestMethodFailureException;

#[CoversClass(TestMethodState::class)]
#[UsesClass(TestMethodFailureException::class)]
#[Group('runtime')]
final class TestMethodStateTest extends TestCase
{
    protected function tearDown(): void
    {
        $reflection = new ReflectionClass(TestMethodState::class);

        $throwables = $reflection->getProperty('throwables');
        $throwables->setValue(null, []);
    }

    public function testInitialStateIsSuccessAndThrowDoesNothingWhenNoFailure(): void
    {
        $state = new TestMethodState();

        self::assertTrue($state->isSuccess('SomeClass', 'someMethod'));
        self::assertFalse($state->isFailed('SomeClass', 'someMethod'));
        self::assertNull($state->failure('SomeClass', 'someMethod'));

        $state->throw('SomeClass', 'someMethod');
    }

    public function testFailMarksStateAsFailedForClassAndMethod(): void
    {
        $state = new TestMethodState();
        $state->fail('SomeClass', 'someMethod', new RuntimeException('some error happened'));

        self::assertFalse($state->isSuccess('SomeClass', 'someMethod'));
        self::assertTrue($state->isFailed('SomeClass', 'someMethod'));
    }

    public function testFailureReturnsExceptionIfClassMethodWasRegistered(): void
    {
        $state = new TestMethodState();
        $state->fail('SomeClass', 'someMethod', new RuntimeException('some error happened'));

        self::assertInstanceOf(TestMethodFailureException::class, $state->failure('SomeClass', 'someMethod'));
    }

    public function testFailureReturnsNothingIfClassMethodIsUnknown(): void
    {
        $state = new TestMethodState();
        $state->fail('SomeClass', 'someMethod', new RuntimeException('some error happened'));

        self::assertNull($state->failure('OtherClass', 'someMethod'));
        self::assertNull($state->failure('SomeClass', 'OtherMethod'));
        self::assertInstanceOf(TestMethodFailureException::class, $state->failure('SomeClass', 'someMethod'));
    }

    public function testFailureClearsStoredThrowableAfterReading(): void
    {
        $state = new TestMethodState();
        $state->fail('SomeClass', 'someMethod', new RuntimeException('some error happened'));

        self::assertInstanceOf(TestMethodFailureException::class, $state->failure('SomeClass', 'someMethod'));
        self::assertTrue($state->isSuccess('SomeClass', 'someMethod'));
        self::assertFalse($state->isFailed('SomeClass', 'someMethod'));
        self::assertNull($state->failure('SomeClass', 'someMethod'));
    }

    public function testFailureIsTrackedPerClassAndMethod(): void
    {
        $state = new TestMethodState();
        $state->fail('ClassA', 'methodOne', new RuntimeException('A'));
        $state->fail('ClassA', 'methodTwo', new RuntimeException('B'));
        $state->fail('ClassB', 'methodOne', new RuntimeException('C'));

        self::assertTrue($state->isFailed('ClassA', 'methodOne'));
        self::assertTrue($state->isFailed('ClassA', 'methodTwo'));
        self::assertTrue($state->isFailed('ClassB', 'methodOne'));
        self::assertFalse($state->isFailed('ClassB', 'methodTwo'));
    }

    public function testFailureThrowWhenFailureMatchesClassMethod(): void
    {
        $state = new TestMethodState();
        $state->fail('SomeClass', 'someMethod', new RuntimeException('Boom'));

        $this->expectException(TestMethodFailureException::class);

        $state->throw('SomeClass', 'someMethod');
    }
}
