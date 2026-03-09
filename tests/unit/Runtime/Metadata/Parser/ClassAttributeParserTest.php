<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Tests\Unit\Runtime\Metadata;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Scenario\Core\Attribute\AsScenario;
use Scenario\Core\Runtime\Metadata\Parser\ClassAttributeParser;
use Scenario\Core\Tests\Files\InvalidScenario;
use Scenario\Core\Tests\Files\ValidScenario;

#[CoversClass(ClassAttributeParser::class)]
#[UsesClass(AsScenario::class)]
#[UsesClass(ValidScenario::class)]
#[UsesClass(InvalidScenario::class)]
#[Group('runtime')]
final class ClassAttributeParserTest extends TestCase
{
    public function testParseReturnsClassAttributes(): void
    {
        $attributes = new ClassAttributeParser()->parse(ValidScenario::class);

        self::assertCount(1, $attributes);
        self::assertSame(AsScenario::class, $attributes[0]->getName());
        self::assertInstanceOf(AsScenario::class, $attributes[0]->newInstance());
    }

    public function testParseReturnsEmptyArrayWhenNoAttributes(): void
    {
        self::assertSame([], new ClassAttributeParser()->parse(InvalidScenario::class));
    }
}
