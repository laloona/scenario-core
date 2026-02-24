<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Scenario\Core\Contract\ScenarioInterface;
use Scenario\Core\Runtime\Exception\ScenarioBuilderException;
use Scenario\Core\Runtime\ScenarioBuilder;
use Scenario\Core\Tests\Files\InvalidScenario;
use Scenario\Core\Tests\Files\ValidScenario;

#[CoversClass(ScenarioBuilder::class)]
#[Group('runtime')]
final class ScenarioBuilderTest extends TestCase
{
    public function testValidScenarioBuild(): void
    {
        $scenarioObject = new ScenarioBuilder()->build(ValidScenario::class);

        self::assertInstanceOf(ScenarioInterface::class, $scenarioObject);
    }

    public function IntestValidScenarioBuild(): void
    {
        self::expectException(ScenarioBuilderException::class);

        $scenarioObject = new ScenarioBuilder()->build(InvalidScenario::class);
    }
}
