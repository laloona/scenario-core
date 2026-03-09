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
use Scenario\Core\Runtime\Application\TestClassState;
use Scenario\Core\Runtime\Exception\TestClassFailureException;

#[CoversClass(TestClassState::class)]
#[UsesClass(TestClassFailureException::class)]
#[Group('runtime')]
final class TestClassStateTest extends TestCase
{
    protected function tearDown(): void
    {
        $reflection = new ReflectionClass(TestClassState::class);

        $throwables = $reflection->getProperty('throwables');
        $throwables->setValue(null, []);
    }

    public function testInitialStateIsSuccessAndThrowDoesNothingWhenNoFailure(): void
    {
        $state = new TestClassState();

        self::assertTrue($state->isSuccess('SomeClass'));
        self::assertFalse($state->isFailed('SomeClass'));
        self::assertNull($state->failure('SomeClass'));

        $state->throw('SomeCLass');
    }

    public function testFailMarksStateAsFailedForClass(): void
    {
        $state = new TestClassState();
        $state->fail('SomeClass', new RuntimeException('some error happened'));

        self::assertFalse($state->isSuccess('SomeClass'));
        self::assertTrue($state->isFailed('SomeClass'));
        self::assertInstanceOf(TestClassFailureException::class, $state->failure('SomeClass'));
    }

    public function testFailureReturnsNullIfClassNotRegisteredAndThrowDoesNothingWhenClassIsNotRegistered(): void
    {
        $state = new TestClassState();
        $state->fail('SomeClass', new RuntimeException('some error happened'));

        self::assertNull($state->failure('OtherClass'));
        self::assertTrue($state->isSuccess('OtherClass'));
        self::assertFalse($state->isFailed('OtherClass'));
    }

    public function testFailureReturnsExceptionIfClassWasRegisteredAndThrowWhenFailureMatchesClass(): void
    {
        $state = new TestClassState();
        $state->fail('SomeClass', new RuntimeException('some error happened'));

        $this->expectException(TestClassFailureException::class);

        $state->throw('SomeClass');
    }

    public function testFailuresAreTrackedPerClass(): void
    {
        $state = new TestClassState();

        $state->fail('ClassA', new RuntimeException('A'));
        $state->fail('ClassB', new RuntimeException('B'));

        self::assertTrue($state->isFailed('ClassA'));
        self::assertTrue($state->isFailed('ClassB'));
        self::assertFalse($state->isFailed('ClassC'));
    }
}
