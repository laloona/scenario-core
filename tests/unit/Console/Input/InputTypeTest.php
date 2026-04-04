<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Tests\Unit\Console\Input;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Stateforge\Scenario\Core\Console\Input\InputType;
use Stateforge\Scenario\Core\Runtime\Metadata\ValueType\BooleanType;
use Stateforge\Scenario\Core\Runtime\Metadata\ValueType\FloatType;
use Stateforge\Scenario\Core\Runtime\Metadata\ValueType\IntegerType;
use Stateforge\Scenario\Core\Runtime\Metadata\ValueType\StringType;

#[CoversClass(InputType::class)]
#[UsesClass(StringType::class)]
#[UsesClass(IntegerType::class)]
#[UsesClass(FloatType::class)]
#[UsesClass(BooleanType::class)]
#[Group('console')]
#[Small]
final class InputTypeTest extends TestCase
{
    public function testStringCastRemovesWrappingQuotes(): void
    {
        self::assertSame('value', InputType::String->cast('"value"'));
    }

    public function testIntegerAndFloatCastNumericStrings(): void
    {
        self::assertSame(10, InputType::Integer->cast('10'));
        self::assertSame(10.5, InputType::Float->cast('10.5'));
    }

    public function testBooleanCastUnderstandsCommonStringValues(): void
    {
        self::assertTrue(InputType::Boolean->cast('yes'));
        self::assertFalse(InputType::Boolean->cast('off'));
    }

    public function testCastReturnsNullForInvalidValues(): void
    {
        self::assertNull(InputType::Integer->cast('ten'));
        self::assertNull(InputType::Float->cast('ten.point.five'));
        self::assertNull(InputType::Boolean->cast('maybe'));
    }
}
