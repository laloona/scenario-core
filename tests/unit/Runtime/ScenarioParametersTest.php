<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Tests\Unit\Runtime;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Stateforge\Scenario\Core\Attribute\Parameter;
use Stateforge\Scenario\Core\Runtime\Exception\MissingRequiredParametersException;
use Stateforge\Scenario\Core\Runtime\Exception\NotAllowedParametersException;
use Stateforge\Scenario\Core\Runtime\Exception\ParameterValueErrorException;
use Stateforge\Scenario\Core\Runtime\Exception\UndefinedParameterException;
use Stateforge\Scenario\Core\Runtime\Metadata\ParameterType;
use Stateforge\Scenario\Core\Runtime\Metadata\ValueType\BooleanType;
use Stateforge\Scenario\Core\Runtime\Metadata\ValueType\FloatType;
use Stateforge\Scenario\Core\Runtime\Metadata\ValueType\IntegerType;
use Stateforge\Scenario\Core\Runtime\Metadata\ValueType\StringType;
use Stateforge\Scenario\Core\Runtime\ScenarioParameters;

#[CoversClass(ScenarioParameters::class)]
#[UsesClass(BooleanType::class)]
#[UsesClass(FloatType::class)]
#[UsesClass(IntegerType::class)]
#[UsesClass(MissingRequiredParametersException::class)]
#[UsesClass(NotAllowedParametersException::class)]
#[UsesClass(Parameter::class)]
#[UsesClass(ParameterType::class)]
#[UsesClass(ParameterValueErrorException::class)]
#[UsesClass(StringType::class)]
#[UsesClass(UndefinedParameterException::class)]
#[Group('runtime')]
#[Small]
final class ScenarioParametersTest extends TestCase
{
    public function testReturnsConfiguredValue(): void
    {
        $parameters = new ScenarioParameters(
            [
                new Parameter('name', ParameterType::String),
            ],
            [
                'name' => 'MyName',
            ],
        );

        self::assertSame('MyName', $parameters->get('name'));
    }

    public function testReturnsDefaultValueWhenParameterWasNotPassed(): void
    {
        $parameters = new ScenarioParameters(
            [
                new Parameter('myint', ParameterType::Integer, null, false, false, 100),
            ],
            [],
        );

        self::assertSame(100, $parameters->get('myint'));
    }

    public function testUsesDefaultValueWhenPassedValueIsEmptyString(): void
    {
        $parameters = new ScenarioParameters(
            [
                new Parameter('myint', ParameterType::Integer, null, false, false, 100),
            ],
            [
                'myint' => '',
            ],
        );

        self::assertSame(100, $parameters->get('myint'));
    }

    public function testCastsConfiguredValueToExpectedType(): void
    {
        $parameters = new ScenarioParameters(
            [
                new Parameter('myint', ParameterType::Integer, null, false, false),
                new Parameter('mybool', ParameterType::Boolean, null, false, false),
                new Parameter('myfloat', ParameterType::Float, null, false, false),
            ],
            [
                'myint' => '42',
                'mybool' => 'true',
                'myfloat' => '1.5',
            ],
        );

        self::assertSame(42, $parameters->get('myint'));
        self::assertTrue($parameters->get('mybool'));
        self::assertSame(1.5, $parameters->get('myfloat'));
    }

    public function testThrowsExceptionForNotAllowedParameters(): void
    {
        $this->expectException(NotAllowedParametersException::class);

        new ScenarioParameters(
            [
                new Parameter('name', ParameterType::String),
            ],
            [
                'name' => 'MyName',
                'unknown' => 'nope',
            ],
        );
    }

    public function testThrowsExceptionWhenRequiredParameterIsMissing(): void
    {
        $this->expectException(MissingRequiredParametersException::class);

        new ScenarioParameters(
            [
                new Parameter('name', ParameterType::String, null, true),
            ],
            [],
        );
    }

    public function testThrowsExceptionWhenParameterValueHasWrongType(): void
    {
        $this->expectException(ParameterValueErrorException::class);

        new ScenarioParameters(
            [
                new Parameter('myint', ParameterType::Integer, null, false, false),
            ],
            [
                'myint' => 'abc',
            ],
        );
    }

    public function testAllReturnsCastedValuesForEachParameter(): void
    {
        $parameters = new ScenarioParameters(
            [
                new Parameter('myint', ParameterType::Integer, null, false, false, 1),
                new Parameter('mybool', ParameterType::Boolean, null, false, false),
            ],
            [
                'myint' => '2',
                'mybool' => 'false',
            ],
        );

        self::assertSame(
            [
                'myint' => 2,
                'mybool' => false,
            ],
            $parameters->all(),
        );
    }

    public function testOptionalNullValueIsAccepted(): void
    {
        $parameters = new ScenarioParameters(
            [
                new Parameter('name', ParameterType::String, null, false),
            ],
            [
                'name' => null,
            ],
        );

        self::assertNull($parameters->get('name'));
    }

    public function testRequiredParameterWithEmptyStringThrowsValueErrorException(): void
    {
        $this->expectException(ParameterValueErrorException::class);

        new ScenarioParameters(
            [
                new Parameter('name', ParameterType::String, null, true),
            ],
            [
                'name' => '',
            ],
        );
    }

    public function testGetThrowsExceptionForUndefinedParameter(): void
    {
        $parameters = new ScenarioParameters(
            [
                new Parameter('name', ParameterType::String),
            ],
            [
                'name' => 'MyName',
            ],
        );

        $this->expectException(UndefinedParameterException::class);

        $parameters->get('unknown');
    }

    public function testAllReturnsDefaultsForMissingOptionalParameters(): void
    {
        $parameters = new ScenarioParameters(
            [
                new Parameter('name', ParameterType::String, null, false, false, 'fallback'),
                new Parameter('count', ParameterType::Integer, null, false, false, 5),
            ],
            [
                'name' => 'configured',
            ],
        );

        self::assertSame(
            [
                'name' => 'configured',
                'count' => 5,
            ],
            $parameters->all(),
        );
    }
}
