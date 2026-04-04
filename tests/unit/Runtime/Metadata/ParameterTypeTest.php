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
use Stateforge\Scenario\Core\Runtime\Metadata\ParameterType;
use Stateforge\Scenario\Core\Runtime\Metadata\ValueType\BooleanType;
use Stateforge\Scenario\Core\Runtime\Metadata\ValueType\FloatType;
use Stateforge\Scenario\Core\Runtime\Metadata\ValueType\IntegerType;
use Stateforge\Scenario\Core\Runtime\Metadata\ValueType\StringType;

#[CoversClass(ParameterType::class)]
#[UsesClass(BooleanType::class)]
#[UsesClass(FloatType::class)]
#[UsesClass(IntegerType::class)]
#[UsesClass(StringType::class)]
#[Group('runtime')]
#[Small]
final class ParameterTypeTest extends TestCase
{
    public function testStringCastsQuotedInputAndFormatsAsString(): void
    {
        self::assertSame('value', ParameterType::String->cast('"value"'));
        self::assertTrue(ParameterType::String->valid('value'));
        self::assertFalse(ParameterType::String->valid(10));
        self::assertSame('value', ParameterType::String->asString("'value'"));
    }

    public function testIntegerCastsStringInputAndFormatsAsString(): void
    {
        self::assertSame(10, ParameterType::Integer->cast('10'));
        self::assertTrue(ParameterType::Integer->valid('10'));
        self::assertFalse(ParameterType::Integer->valid('ten'));
        self::assertSame('10', ParameterType::Integer->asString(10));
    }

    public function testFloatCastsStringInputAndFormatsAsString(): void
    {
        self::assertSame(10.5, ParameterType::Float->cast('10.5'));
        self::assertTrue(ParameterType::Float->valid('10.5'));
        self::assertFalse(ParameterType::Float->valid('ten.point.five'));
        self::assertSame('10.5', ParameterType::Float->asString(10.5));
    }

    public function testBooleanCastsCommonStringValuesAndFormatsAsString(): void
    {
        self::assertTrue(ParameterType::Boolean->cast('yes'));
        self::assertFalse(ParameterType::Boolean->cast('off'));
        self::assertTrue(ParameterType::Boolean->valid('true'));
        self::assertFalse(ParameterType::Boolean->valid('maybe'));
        self::assertSame('1', ParameterType::Boolean->asString(true));
        self::assertSame('0', ParameterType::Boolean->asString('off'));
    }

    public function testNullInputReturnsNullAndIsInvalidForEveryType(): void
    {
        foreach (ParameterType::cases() as $type) {
            self::assertNull($type->cast(null));
            self::assertFalse($type->valid(null));
            self::assertNull($type->asString(null));
        }
    }
}
