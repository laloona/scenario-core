<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Tests\Unit\Runtime\Metadata\ValueType;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Scenario\Core\Runtime\Metadata\ValueType\IntegerType;

#[CoversClass(IntegerType::class)]
#[Group('runtime')]
#[Small]
final class IntegerTypeTest extends TestCase
{
    public function testKeepsIntegerInput(): void
    {
        $type = new IntegerType(10);

        self::assertSame(10, $type->value);
        self::assertSame('10', $type->asString());
    }

    public function testCastsNumericStringToInteger(): void
    {
        $type = new IntegerType('42');

        self::assertSame(42, $type->value);
        self::assertSame('42', $type->asString());
    }

    public function testReturnsNullForInvalidInput(): void
    {
        $type = new IntegerType('ten');

        self::assertNull($type->value);
        self::assertNull($type->asString());
    }
}
