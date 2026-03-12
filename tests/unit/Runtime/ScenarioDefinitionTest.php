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

use PHPUnit\Framework\TestCase;
use Scenario\Core\Attribute\AsScenario;
use Scenario\Core\Runtime\ScenarioDefinition;
use Scenario\Core\Tests\Files\ValidScenario;

final class ScenarioDefinitionTest extends TestCase
{
    public function testNameIsTakenFromAttribute(): void
    {
        $definition = new ScenarioDefinition(
            'suite',
            ValidScenario::class,
            new AsScenario('alias', 'description'),
            [],
        );

        self::assertSame('alias', $definition->name);
    }

    public function testIsSameReturnsTrueForClassName(): void
    {
        $definition = new ScenarioDefinition(
            'suite',
            ValidScenario::class,
            new AsScenario('alias', null),
            [],
        );

        self::assertTrue($definition->isSame(ValidScenario::class));
    }

    public function testIsSameReturnsTrueForAlias(): void
    {
        $definition = new ScenarioDefinition(
            'suite',
            ValidScenario::class,
            new AsScenario('alias', null),
            [],
        );

        self::assertTrue($definition->isSame('alias'));
    }

    public function testIsSameReturnsFalseForDifferentName(): void
    {
        $definition = new ScenarioDefinition(
            'suite',
            ValidScenario::class,
            new AsScenario('alias', null),
            [],
        );

        self::assertFalse($definition->isSame('other'));
    }
}
