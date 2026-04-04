<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Tests\Unit\Runtime\Metadata;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Stateforge\Scenario\Core\Runtime\Exception\Metadata\CycleException;
use Stateforge\Scenario\Core\Runtime\Exception\Metadata\SwitchDryRunAttributeContextException;
use Stateforge\Scenario\Core\Runtime\Metadata\AttributeContext;
use Stateforge\Scenario\Core\Runtime\Metadata\ContextTarget;
use Stateforge\Scenario\Core\Runtime\Metadata\ExecutionType;
use Stateforge\Scenario\Core\Tests\Files\AnotherScenario;
use Stateforge\Scenario\Core\Tests\Files\ValidScenario;
use Stateforge\Scenario\Core\Tests\Unit\AttributeContextMock;
use stdClass;

#[CoversClass(AttributeContext::class)]
#[UsesClass(CycleException::class)]
#[UsesClass(SwitchDryRunAttributeContextException::class)]
#[Group('runtime')]
#[Small]
final class AttributeContextTest extends TestCase
{
    use AttributeContextMock;

    public function setUp(): void
    {
        $this->resetAttributeContext();
    }

    public function testOnClassContext(): void
    {
        $context = AttributeContext::getInstance(
            stdClass::class,
            null,
            ExecutionType::Up,
            false,
            null,
        );

        self::assertSame(ContextTarget::OnClass, $context->target());
        self::assertTrue($context->onClass());
        self::assertFalse($context->onMethod());
    }

    public function testOnMethodContext(): void
    {
        $context = AttributeContext::getInstance(
            stdClass::class,
            'myMethod',
            ExecutionType::Up,
            false,
            null,
        );

        self::assertSame(ContextTarget::OnMethod, $context->target());
        self::assertFalse($context->onClass());
        self::assertTrue($context->onMethod());
    }

    public function testReadonlyPropertiesAreExposed(): void
    {
        $context = AttributeContext::getInstance(
            stdClass::class,
            'myMethod',
            ExecutionType::Down,
            false,
            null,
        );

        self::assertSame(stdClass::class, $context->class);
        self::assertSame('myMethod', $context->method);
        self::assertSame(ExecutionType::Down, $context->executionType);
    }

    public function testGetInstanceCachesPerMethodAndExecutionType(): void
    {
        $first = AttributeContext::getInstance(
            stdClass::class,
            'myMethod',
            ExecutionType::Up,
            false,
            null,
        );

        $second = AttributeContext::getInstance(
            stdClass::class,
            'myMethod',
            ExecutionType::Up,
            false,
            null,
        );

        $third = AttributeContext::getInstance(
            stdClass::class,
            'myMethod',
            ExecutionType::Down,
            false,
            null,
        );

        self::assertSame($first, $second);
        self::assertNotSame($first, $third);
    }

    public function testGetInstanceResetsCacheWhenClassChanges(): void
    {
        $first = AttributeContext::getInstance(
            stdClass::class,
            null,
            ExecutionType::Up,
            false,
            null,
        );

        $second = AttributeContext::getInstance(
            self::class,
            null,
            ExecutionType::Up,
            false,
            null,
        );

        self::assertNotSame($first, $second);
    }

    public function testAuditTracksScenariosAndThrowsOnCycle(): void
    {
        $context = AttributeContext::getInstance(
            stdClass::class,
            null,
            ExecutionType::Up,
            false,
            null,
        );

        $context->audit(ValidScenario::class, ['foo' => 'bar']);
        self::assertCount(1, $context->getAudits());

        $this->expectException(CycleException::class);
        $context->audit(ValidScenario::class, ['foo' => 'bar']);
    }

    public function testAuditStoresSignatureWithoutParameters(): void
    {
        $context = AttributeContext::getInstance(
            stdClass::class,
            null,
            ExecutionType::Down,
            false,
            null,
        );

        $context->audit(ValidScenario::class, []);

        self::assertSame([ValidScenario::class], $context->getAudits());
    }

    public function testGetInstanceCachesPerExecutionTypeOnClassContext(): void
    {
        $up = AttributeContext::getInstance(
            AnotherScenario::class,
            null,
            ExecutionType::Up,
            true,
            null,
        );

        $down = AttributeContext::getInstance(
            AnotherScenario::class,
            null,
            ExecutionType::Down,
            true,
            null,
        );

        self::assertNotSame($up, $down);
    }

    public function testGetInstanceCachesOnClassContextSameExecutionType(): void
    {
        $first = AttributeContext::getInstance(
            AnotherScenario::class,
            null,
            ExecutionType::Up,
            false,
            null,
        );

        $second = AttributeContext::getInstance(
            AnotherScenario::class,
            null,
            ExecutionType::Up,
            false,
            null,
        );

        self::assertSame($first, $second);
    }

    public function testGetInstanceOnClassContextWithDryRunSwitch(): void
    {
        AttributeContext::getInstance(
            ValidScenario::class,
            null,
            ExecutionType::Up,
            true,
            null,
        );

        $this->expectException(SwitchDryRunAttributeContextException::class);
        $this->expectExceptionMessage('context switch not allowed, found switch from dryRun to regular');

        AttributeContext::getInstance(
            ValidScenario::class,
            null,
            ExecutionType::Up,
            false,
            null,
        );
    }

    public function testGetInstanceOnMethodContextWithDryRunSwitch(): void
    {
        AttributeContext::getInstance(
            ValidScenario::class,
            'up',
            ExecutionType::Up,
            true,
            null,
        );

        $this->expectException(SwitchDryRunAttributeContextException::class);
        $this->expectExceptionMessage('context switch not allowed, found switch from dryRun to regular');

        AttributeContext::getInstance(
            ValidScenario::class,
            'up',
            ExecutionType::Up,
            false,
            null,
        );
    }

    public function testAuditAllowsDifferentParameters(): void
    {
        $context = AttributeContext::getInstance(
            stdClass::class,
            null,
            ExecutionType::Up,
            false,
            null,
        );

        $context->audit(ValidScenario::class, ['foo' => 'bar']);
        $context->audit(ValidScenario::class, ['foo' => 'baz']);

        self::assertCount(2, $context->getAudits());
    }

    public function testGetAuditsStartsEmpty(): void
    {
        $context = AttributeContext::getInstance(
            stdClass::class,
            'method',
            ExecutionType::Down,
            false,
            null,
        );

        self::assertSame([], $context->getAudits());
    }

    public function testDryRunPropertyIsExposed(): void
    {
        $context = AttributeContext::getInstance(
            AnotherScenario::class,
            'down',
            ExecutionType::Down,
            true,
            null,
        );

        self::assertTrue($context->dryRun);
    }

    public function testCloneMethodCanBeInvokedViaReflection(): void
    {
        $context = AttributeContext::getInstance(
            AnotherScenario::class,
            'down',
            ExecutionType::Down,
            true,
            null,
        );

        $cloneMethod = new ReflectionMethod($context, '__clone');
        $cloneMethod->invoke($context);

        self::addToAssertionCount(1);
    }
}
