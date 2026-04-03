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
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Scenario\Core\Attribute\Parameter;
use Scenario\Core\Runtime\Metadata\ParameterType;
use Scenario\Core\Runtime\Metadata\ValueType\IntegerType;
use Scenario\Core\Runtime\ScenarioParameters;
use Scenario\Core\Scenario;

#[CoversClass(Scenario::class)]
#[UsesClass(IntegerType::class)]
#[UsesClass(ScenarioParameters::class)]
#[UsesClass(Parameter::class)]
#[UsesClass(ParameterType::class)]
#[Group('scenario')]
#[Small]
final class ScenarioTest extends TestCase
{
    public function testConfigureStoresParametersAndParameterReturnsResolvedValue(): void
    {
        $scenario = new class () extends Scenario {
            public function up(): void
            {
            }
        };

        $parameters = new ScenarioParameters(
            [new Parameter('limit', ParameterType::Integer)],
            ['limit' => '12'],
        );

        $scenario->configure($parameters);

        self::assertSame(12, $scenario->parameter('limit'));
    }

    public function testDownHasEmptyDefaultImplementation(): void
    {
        $scenario = new class () extends Scenario {
            public function up(): void
            {
            }
        };

        $scenario->down();

        self::addToAssertionCount(1);
    }
}
