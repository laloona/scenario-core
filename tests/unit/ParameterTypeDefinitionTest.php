<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Tests\Unit;

use GlobalIntegerParameterType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Stateforge\Scenario\Core\ParameterTypeDefinition;
use Stateforge\Scenario\Core\Runtime\Metadata\ValueType\IntegerType;
use Stateforge\Scenario\Core\Tests\Files\IntegerParameterType;

#[CoversClass(ParameterTypeDefinition::class)]
#[UsesClass(IntegerType::class)]
#[Group('runtime')]
#[Small]
final class ParameterTypeDefinitionTest extends TestCase
{
    public function testConstructSetsNameAndValueFromDefinition(): void
    {
        $type = new IntegerParameterType();

        self::assertSame('IntegerParameterType', $type->name);
        self::assertSame(IntegerParameterType::class, $type->value);
    }

    public function testConstructSetsNameAndValueFromDefinitionWithoutNamespace(): void
    {
        $type = new GlobalIntegerParameterType();

        self::assertSame('GlobalIntegerParameterType', $type->name);
        self::assertSame('GlobalIntegerParameterType', $type->value);
    }

    public function testValidReturnsTrueOnlyForCastableValues(): void
    {
        $type = new IntegerParameterType();

        self::assertTrue($type->valid(10));
        self::assertTrue($type->valid('10'));
        self::assertFalse($type->valid('ten'));
        self::assertFalse($type->valid(null));
    }

    public function testAsStringFormatsCastedValueAndReturnsNullForInvalidInput(): void
    {
        $type = new IntegerParameterType();

        self::assertSame('10', $type->asString('10'));
        self::assertNull($type->asString('ten'));
    }
}
