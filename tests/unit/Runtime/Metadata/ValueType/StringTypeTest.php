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
use Scenario\Core\Runtime\Metadata\ValueType\StringType;

#[CoversClass(StringType::class)]
#[Group('runtime')]
#[Small]
final class StringTypeTest extends TestCase
{
    public function testKeepsPlainStringInput(): void
    {
        $type = new StringType('value');

        self::assertSame('value', $type->value);
        self::assertSame('value', $type->asString());
    }

    public function testRemovesWrappingQuotes(): void
    {
        self::assertSame('value', (new StringType('"value"'))->value);
        self::assertSame('value', (new StringType("'value'"))->asString());
    }

    public function testReturnsNullForInvalidInput(): void
    {
        $type = new StringType(10);

        self::assertNull($type->value);
        self::assertNull($type->asString());
    }
}
