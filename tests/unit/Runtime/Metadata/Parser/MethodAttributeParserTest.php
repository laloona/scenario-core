<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Tests\Runtime\Metadata;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Scenario\Core\Attribute\ApplyScenario;
use Scenario\Core\Runtime\Metadata\Parser\MethodAttributeParser;
use Scenario\Core\Tests\Files\InvalidScenario;
use Scenario\Core\Tests\Files\ValidScenario;

#[CoversClass(MethodAttributeParser::class)]
#[UsesClass(ApplyScenario::class)]
#[UsesClass(ValidScenario::class)]
#[UsesClass(InvalidScenario::class)]
#[Group('runtime')]
final class MethodAttributeParserTest extends TestCase
{
    public function testParseReturnsMethodAttributes(): void
    {
        $attributes = new MethodAttributeParser()->parse(ValidScenario::class, 'up');

        self::assertCount(1, $attributes);
        self::assertSame(ApplyScenario::class, $attributes[0]->getName());
        self::assertInstanceOf(ApplyScenario::class, $attributes[0]->newInstance());
    }

    public function testParseReturnsEmptyArrayWhenNoAttributes(): void
    {
        self::assertSame([], new MethodAttributeParser()->parse(InvalidScenario::class, 'up'));
    }
}
