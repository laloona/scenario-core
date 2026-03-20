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
use Scenario\Core\Attribute\AsScenario;
use Scenario\Core\Attribute\Parameter;
use Scenario\Core\Runtime\ScenarioDefinition;
use Scenario\Core\Tests\Files\ValidScenario;

#[CoversClass(ScenarioDefinition::class)]
#[UsesClass(AsScenario::class)]
#[UsesClass(Parameter::class)]
#[Group('runtime')]
#[Small]
final class ScenarioDefinitionTest extends TestCase
{
    public function testNameMatchesAttributeNameWhenValueIsSet(): void
    {
        $attribute = new AsScenario('my-scenario');
        $definition = new ScenarioDefinition('main', ValidScenario::class, $attribute, []);

        self::assertSame('my-scenario', $definition->name);
        self::assertSame($attribute->name, $definition->name);
    }

    public function testNameMatchesAttributeNameWhenValueIsNull(): void
    {
        $attribute = new AsScenario();
        $definition = new ScenarioDefinition('main', ValidScenario::class, $attribute, []);

        self::assertNull($definition->name);
        self::assertSame($attribute->name, $definition->name);
    }
}
