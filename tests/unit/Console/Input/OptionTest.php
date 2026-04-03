<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Tests\Unit\Console\Input;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Scenario\Core\Console\Exception\OptionValueErrorException;
use Scenario\Core\Console\Input\InputType;
use Scenario\Core\Console\Input\Option;
use Scenario\Core\Runtime\Metadata\ValueType\BooleanType;
use Scenario\Core\Runtime\Metadata\ValueType\IntegerType;
use Scenario\Core\Runtime\Metadata\ValueType\StringType;

#[CoversClass(Option::class)]
#[UsesClass(InputType::class)]
#[UsesClass(BooleanType::class)]
#[UsesClass(IntegerType::class)]
#[UsesClass(StringType::class)]
#[UsesClass(OptionValueErrorException::class)]
#[Group('console')]
#[Small]
final class OptionTest extends TestCase
{
    public function testCastReturnsTypedScalarValue(): void
    {
        $option = new Option('verbose', InputType::Boolean);

        self::assertTrue($option->cast('true'));
    }

    public function testCastReturnsDefaultWhenValueIsNull(): void
    {
        $option = new Option('limit', InputType::Integer, false, false, null, '12');

        self::assertSame(12, $option->cast(null));
    }

    public function testRepeatableOptionCastsArrayValues(): void
    {
        $option = new Option('tag', InputType::String, false, true);

        self::assertSame(['first', 'second'], $option->cast(['first', '"second"']));
    }

    public function testRepeatableOptionUsesCastedDefaultArrayWhenValueIsNull(): void
    {
        $option = new Option('tag', InputType::String, false, true, null, ['first', '"second"']);

        self::assertSame(['first', 'second'], $option->cast(null));
    }

    public function testOptionWithoutDefaultReturnsNullForNullValue(): void
    {
        $option = new Option('limit', InputType::Integer);

        self::assertNull($option->cast(null));
    }

    public function testConstructorThrowsForInvalidScalarDefault(): void
    {
        $this->expectException(OptionValueErrorException::class);
        $this->expectExceptionMessage('wrong default value for option limit, expected type integer but got string');

        new Option('limit', InputType::Integer, false, false, null, 'ten');
    }

    public function testConstructorThrowsWhenRepeatableDefaultIsNotArray(): void
    {
        $this->expectException(OptionValueErrorException::class);
        $this->expectExceptionMessage('wrong default value for option tag, expected type array but got string');

        new Option('tag', InputType::String, false, true, null, 'single');
    }

    public function testConstructorThrowsWhenRepeatableDefaultContainsInvalidValue(): void
    {
        $this->expectException(OptionValueErrorException::class);
        $this->expectExceptionMessage('wrong default value for option limit, expected type integer but got string');

        new Option('limit', InputType::Integer, false, true, null, ['10', 'ten']);
    }

    public function testCastThrowsForMissingRequiredValue(): void
    {
        $option = new Option('path', InputType::String, true);

        $this->expectException(OptionValueErrorException::class);
        $this->expectExceptionMessage('wrong value for option path, expected type string but got NULL');

        $option->cast(null);
    }

    public function testCastThrowsWhenRepeatableOptionReceivesScalar(): void
    {
        $option = new Option('tag', InputType::String, false, true);

        $this->expectException(OptionValueErrorException::class);
        $this->expectExceptionMessage('wrong value for option tag, expected type array but got string');

        $option->cast('single');
    }

    public function testCastThrowsWhenRepeatableOptionReceivesInvalidArrayValue(): void
    {
        $option = new Option('limit', InputType::Integer, false, true);

        $this->expectException(OptionValueErrorException::class);
        $this->expectExceptionMessage('wrong value for option limit, expected type integer but got string');

        $option->cast(['10', 'ten']);
    }
}
