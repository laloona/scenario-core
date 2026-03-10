<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Tests\Unit\Runtime;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Scenario\Core\Attribute\Parameter;
use Scenario\Core\Runtime\Exception\MissingRequiredParametersException;
use Scenario\Core\Runtime\Exception\NotAllowedParametersException;
use Scenario\Core\Runtime\Exception\ParameterValueErrorException;
use Scenario\Core\Runtime\Metadata\ParameterType;
use Scenario\Core\Runtime\ScenarioParameters;

#[CoversClass(ScenarioParameters::class)]
#[UsesClass(Parameter::class)]
#[UsesClass(ParameterType::class)]
#[UsesClass(NotAllowedParametersException::class)]
#[UsesClass(MissingRequiredParametersException::class)]
#[UsesClass(ParameterValueErrorException::class)]
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
                new Parameter('myint', ParameterType::Integer, null, false, 100),
            ],
            [],
        );

        self::assertSame(100, $parameters->get('myint'));
    }

    public function testUsesDefaultValueWhenPassedValueIsEmptyString(): void
    {
        $parameters = new ScenarioParameters(
            [
                new Parameter('myint', ParameterType::Integer, null, false, 100),
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
                new Parameter('myint', ParameterType::Integer, null, false, null),
                new Parameter('mybool', ParameterType::Boolean, null, false, null),
                new Parameter('myfloat', ParameterType::Float, null, false, null),
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
                new Parameter('myint', ParameterType::Integer, null, false, null),
            ],
            [
                'myint' => 'abc',
            ],
        );
    }
}
