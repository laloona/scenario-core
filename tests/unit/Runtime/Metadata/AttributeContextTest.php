<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Tests\Runtime\Metadata;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Scenario\Core\Runtime\Metadata\AttributeContext;
use Scenario\Core\Runtime\Metadata\ContextTarget;
use Scenario\Core\Runtime\Metadata\ExecutionType;
use stdClass;

#[CoversClass(AttributeContext::class)]
#[Group('runtime')]
final class AttributeContextTest extends TestCase
{
    public function testOnClassContext(): void
    {
        $context = new AttributeContext(
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
        $context = new AttributeContext(
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
        $context = new AttributeContext(
            stdClass::class,
            'myMethod',
            ExecutionType::Down,
            false,
        );

        self::assertSame(stdClass::class, $context->class);
        self::assertSame('myMethod', $context->method);
        self::assertSame(ExecutionType::Down, $context->executionType);
    }
}
