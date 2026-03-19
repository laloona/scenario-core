<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Tests\Unit\Attribute;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Scenario\Core\Attribute\Parameter;
use Scenario\Core\Runtime\Exception\ParameterValueErrorException;
use Scenario\Core\Runtime\Metadata\ParameterType;

#[CoversClass(Parameter::class)]
#[UsesClass(ParameterType::class)]
#[UsesClass(ParameterValueErrorException::class)]
#[Group('attribute')]
#[Small]
final class ParameterTest extends TestCase
{
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
}
