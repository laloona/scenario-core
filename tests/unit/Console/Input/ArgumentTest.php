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
use Stateforge\Scenario\Core\Console\Exception\ArgumentValueErrorException;
use Stateforge\Scenario\Core\Console\Input\Argument;
use Stateforge\Scenario\Core\Console\Input\InputType;
use Stateforge\Scenario\Core\Runtime\Metadata\ValueType\FloatType;
use Stateforge\Scenario\Core\Runtime\Metadata\ValueType\IntegerType;
use Stateforge\Scenario\Core\Runtime\Metadata\ValueType\StringType;

#[CoversClass(Argument::class)]
#[UsesClass(ArgumentValueErrorException::class)]
#[UsesClass(InputType::class)]
#[UsesClass(IntegerType::class)]
#[UsesClass(FloatType::class)]
#[UsesClass(StringType::class)]
#[Group('console')]
#[Small]
final class ArgumentTest extends TestCase
{
    public function testCastReturnsTypedValue(): void
    {
        $argument = new Argument('limit', InputType::Integer);

        self::assertSame(10, $argument->cast('10'));
    }

    public function testCastReturnsDefaultWhenValueIsNull(): void
    {
        $argument = new Argument('name', InputType::String, false, null, '"Scenario"');

        self::assertSame('Scenario', $argument->cast(null));
    }

    public function testConstructorThrowsForInvalidDefault(): void
    {
        $this->expectException(ArgumentValueErrorException::class);
        $this->expectExceptionMessage('wrong default value for argument limit, expected type integer but got string');

        new Argument('limit', InputType::Integer, false, null, 'ten');
    }

    public function testCastThrowsForMissingRequiredValue(): void
    {
        $argument = new Argument('enabled', InputType::Boolean, true);

        $this->expectException(ArgumentValueErrorException::class);
        $this->expectExceptionMessage('wrong value for argument enabled, expected type boolean but got NULL');

        $argument->cast(null);
    }

    public function testCastThrowsForInvalidValue(): void
    {
        $argument = new Argument('price', InputType::Float);

        $this->expectException(ArgumentValueErrorException::class);
        $this->expectExceptionMessage('wrong value for argument price, expected type float but got string');

        $argument->cast('not-a-float');
    }
}
