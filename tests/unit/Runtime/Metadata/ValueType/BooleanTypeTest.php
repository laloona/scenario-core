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
use Stateforge\Scenario\Core\Runtime\Metadata\ValueType\BooleanType;

#[CoversClass(BooleanType::class)]
#[Group('runtime')]
#[Small]
final class BooleanTypeTest extends TestCase
{
    public function testKeepsBooleanInput(): void
    {
        $type = new BooleanType(true);

        self::assertTrue($type->value);
        self::assertSame('1', $type->asString());
    }

    public function testCastsCommonStringValues(): void
    {
        self::assertTrue((new BooleanType('yes'))->value);
        self::assertFalse((new BooleanType('off'))->value);
        self::assertSame('0', (new BooleanType('false'))->asString());
    }

    public function testReturnsNullForInvalidInput(): void
    {
        $type = new BooleanType('maybe');

        self::assertNull($type->value);
        self::assertNull($type->asString());
    }
}
