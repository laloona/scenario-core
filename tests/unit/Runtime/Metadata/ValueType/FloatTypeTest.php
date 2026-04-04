<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Tests\Unit\Runtime\Metadata\ValueType;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Stateforge\Scenario\Core\Runtime\Metadata\ValueType\FloatType;

#[CoversClass(FloatType::class)]
#[Group('runtime')]
#[Small]
final class FloatTypeTest extends TestCase
{
    public function testKeepsFloatInput(): void
    {
        $type = new FloatType(10.5);

        self::assertSame(10.5, $type->value);
        self::assertSame('10.5', $type->asString());
    }

    public function testCastsNumericStringToFloat(): void
    {
        $type = new FloatType('42.75');

        self::assertSame(42.75, $type->value);
        self::assertSame('42.75', $type->asString());
    }

    public function testReturnsNullForInvalidInput(): void
    {
        $type = new FloatType('ten.point.five');

        self::assertNull($type->value);
        self::assertNull($type->asString());
    }
}
