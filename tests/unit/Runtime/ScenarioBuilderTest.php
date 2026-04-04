<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Tests\Unit\Runtime;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Stateforge\Scenario\Core\Contract\ScenarioInterface;
use Stateforge\Scenario\Core\Runtime\Exception\WrongScenarioSubclassException;
use Stateforge\Scenario\Core\Runtime\ScenarioBuilder;
use Stateforge\Scenario\Core\Tests\Files\InvalidScenario;
use Stateforge\Scenario\Core\Tests\Files\ValidScenario;

#[CoversClass(ScenarioBuilder::class)]
#[UsesClass(WrongScenarioSubclassException::class)]
#[Group('runtime')]
#[Small]
final class ScenarioBuilderTest extends TestCase
{
    public function testValidScenarioBuild(): void
    {
        self::assertInstanceOf(
            ScenarioInterface::class,
            (new ScenarioBuilder())->build(ValidScenario::class),
        );
    }

    public function testInvalidScenarioBuild(): void
    {
        self::expectException(WrongScenarioSubclassException::class);

        (new ScenarioBuilder())->build(InvalidScenario::class);
    }
}
