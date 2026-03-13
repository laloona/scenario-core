<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Tests\Unit\Runtime\Metadata;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Scenario\Core\Runtime\Exception\CycleException;
use Scenario\Core\Runtime\Metadata\AttributeContext;
use Scenario\Core\Runtime\Metadata\ContextTarget;
use Scenario\Core\Runtime\Metadata\ExecutionType;
use Scenario\Core\Tests\Files\ValidScenario;
use stdClass;

#[CoversClass(AttributeContext::class)]
#[UsesClass(CycleException::class)]
#[Group('runtime')]
#[Small]
final class AttributeContextTest extends TestCase
{
    public function testOnClassContext(): void
    {
        $context = AttributeContext::getInstance(
            stdClass::class,
            null,
            ExecutionType::Up,
            false,
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
        );

        $second = AttributeContext::getInstance(
            stdClass::class,
            'myMethod',
            ExecutionType::Up,
            true,
        );

        $third = AttributeContext::getInstance(
            stdClass::class,
            'myMethod',
            ExecutionType::Down,
            false,
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
        );

        $second = AttributeContext::getInstance(
            self::class,
            null,
            ExecutionType::Up,
            false,
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
        );

        $context->audit(ValidScenario::class, ['foo' => 'bar']);
        self::assertCount(1, $context->getAudits());

        $this->expectException(CycleException::class);
        $context->audit(ValidScenario::class, ['foo' => 'bar']);
    }
}
