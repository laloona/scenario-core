<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Tests\Unit\Attribute;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Stateforge\Scenario\Core\Attribute\Parameter;
use Stateforge\Scenario\Core\ParameterType;
use Stateforge\Scenario\Core\Runtime\Exception\Metadata\ParameterNameErrorException;
use Stateforge\Scenario\Core\Runtime\Exception\Metadata\ParameterValueErrorException;
use Stateforge\Scenario\Core\Runtime\Metadata\ValueType\BooleanType;
use Stateforge\Scenario\Core\Runtime\Metadata\ValueType\FloatType;
use Stateforge\Scenario\Core\Runtime\Metadata\ValueType\IntegerType;
use Stateforge\Scenario\Core\Runtime\Metadata\ValueType\StringType;

#[CoversClass(Parameter::class)]
#[UsesClass(BooleanType::class)]
#[UsesClass(FloatType::class)]
#[UsesClass(IntegerType::class)]
#[UsesClass(ParameterType::class)]
#[UsesClass(ParameterNameErrorException::class)]
#[UsesClass(ParameterValueErrorException::class)]
#[UsesClass(StringType::class)]
#[Group('attribute')]
#[Small]
final class ParameterTest extends TestCase
{
    public function testCastsValidParameterNameSnakeCase(): void
    {
        $parameter = new Parameter('my_int', ParameterType::Integer);

        self::assertSame('my_int', $parameter->name);
    }

    public function testCastsValidParameterNameKebapCase(): void
    {
        $parameter = new Parameter('my-int', ParameterType::Integer);

        self::assertSame('my-int', $parameter->name);
    }

    public function testCastsValidParameterNameCamelCase(): void
    {
        $parameter = new Parameter('myInt', ParameterType::Integer);

        self::assertSame('myInt', $parameter->name);
    }

    public function testThrowsExceptionForInvalidParameterName(): void
    {
        $this->expectException(ParameterNameErrorException::class);

        new Parameter('My Int?', ParameterType::Integer);
    }

    public function testCastsValidDefaultValue(): void
    {
        $parameter = new Parameter('myint', ParameterType::Integer, default: '100');

        self::assertSame(100, $parameter->default);
    }

    public function testThrowsExceptionForInvalidDefaultValue(): void
    {
        $this->expectException(ParameterValueErrorException::class);

        new Parameter('myint', ParameterType::Integer, default: 'abc');
    }

    public function testValidateReturnsTrueForOptionalNull(): void
    {
        $parameter = new Parameter('name', ParameterType::String);

        self::assertTrue($parameter->validate(null));
    }

    public function testValidateReturnsFalseForWrongType(): void
    {
        $parameter = new Parameter('myint', ParameterType::Integer, required: true);

        self::assertFalse($parameter->validate('abc'));
    }

    public function testCastsValidDefaultValueForRepeatable(): void
    {
        $parameter = new Parameter('name', ParameterType::Integer, repeatable: true, default: [ '10', '100']);

        self::assertIsArray($parameter->default);
        self::assertCount(2, $parameter->default);
        self::assertSame(10, $parameter->default[0]);
        self::assertSame(100, $parameter->default[1]);
    }

    public function testThrowsExceptionForAtLeastOneInvalidDefaultValueWhenRepeatable(): void
    {
        $this->expectException(ParameterValueErrorException::class);

        new Parameter('myint', ParameterType::Integer, repeatable: true, default: [ 5, 'abc' ]);
    }

    public function testThrowsExceptionForWhenDefaultValueIsNoArrayWhenRepeatable(): void
    {
        $this->expectException(ParameterValueErrorException::class);

        new Parameter('myint', ParameterType::Integer, repeatable: true, default: 5);
    }

    public function testValidateReturnsTrueForOptionalNullWHenRepeatable(): void
    {
        $parameter = new Parameter('name', ParameterType::String, repeatable: true);

        self::assertTrue($parameter->validate(null));
    }

    public function testValidateReturnsFalseForRequiredNull(): void
    {
        $parameter = new Parameter('myint', ParameterType::Integer, required: true);

        self::assertFalse($parameter->validate(null));
    }

    public function testValidateReturnsTrueForCorrectType(): void
    {
        $parameter = new Parameter('myint', ParameterType::Integer, required: true);

        self::assertTrue($parameter->validate(5));
    }

    public function testValidateReturnsTrueForValidArrayWhenRepeatable(): void
    {
        $parameter = new Parameter('myint', ParameterType::Integer, repeatable: true);

        self::assertTrue($parameter->validate([ 5, 10 ]));
    }

    public function testValidateReturnsFalseForNonArrayWhenRepeatable(): void
    {
        $parameter = new Parameter('myint', ParameterType::Integer, repeatable: true);

        self::assertFalse($parameter->validate(5));
    }

    public function testValidateReturnsFalseForAtLeastOneInvalidValueWhenRepeatable(): void
    {
        $parameter = new Parameter('myint', ParameterType::Integer, repeatable: true);

        self::assertFalse($parameter->validate([ 5, 'abc' ]));
    }

    public function testCastReturnsCastedSingleValue(): void
    {
        $parameter = new Parameter('enabled', ParameterType::Boolean);

        self::assertTrue($parameter->cast('yes'));
    }

    public function testCastReturnsCastedArrayWhenRepeatable(): void
    {
        $parameter = new Parameter('myint', ParameterType::Integer, repeatable: true);

        self::assertSame([ 5, 10 ], $parameter->cast([ '5', '10' ]));
    }

    public function testAsStringReturnsScalarStringRepresentation(): void
    {
        $parameter = new Parameter('enabled', ParameterType::Boolean);

        self::assertSame('0', $parameter->asString(false));
    }

    public function testAsStringReturnsArrayStringRepresentationWhenRepeatable(): void
    {
        $parameter = new Parameter('myint', ParameterType::Integer, repeatable: true);

        self::assertSame('[5,10]', $parameter->asString([ '5', 10 ]));
    }
}
