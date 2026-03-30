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
use Scenario\Core\Contract\ScenarioInterface;
use Scenario\Core\Runtime\Exception\WrongScenarioSubclassException;
use Scenario\Core\Runtime\ScenarioBuilder;
use Scenario\Core\Tests\Files\InvalidScenario;
use Scenario\Core\Tests\Files\ValidScenario;

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
            new ScenarioBuilder()->build(ValidScenario::class),
        );
    }

    public function testInvalidScenarioBuild(): void
    {
        self::expectException(WrongScenarioSubclassException::class);

        new ScenarioBuilder()->build(InvalidScenario::class);
    }
}
