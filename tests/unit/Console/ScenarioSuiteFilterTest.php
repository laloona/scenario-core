<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Tests\Unit\Console;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Stateforge\Scenario\Core\Attribute\AsScenario;
use Stateforge\Scenario\Core\Console\ScenarioSuiteFilter;
use Stateforge\Scenario\Core\Runtime\ScenarioDefinition;
use Stateforge\Scenario\Core\Tests\Files\AnotherScenario;
use Stateforge\Scenario\Core\Tests\Files\ValidScenario;

#[CoversClass(ScenarioSuiteFilter::class)]
#[UsesClass(AsScenario::class)]
#[UsesClass(ScenarioDefinition::class)]
#[Group('console')]
#[Small]
final class ScenarioSuiteFilterTest extends TestCase
{
    public function testFilterReturnsEmptyArrayForNoScenarios(): void
    {
        $filter = new ScenarioSuiteFilter();

        self::assertSame([], $filter->filter([], null));
        self::assertSame([], $filter->filter([], 'main'));
    }

    public function testFilterWithoutSuiteReturnsAllScenariosSortedByKey(): void
    {
        $main = new ScenarioDefinition('main', ValidScenario::class, new AsScenario('zebra'), []);
        $other = new ScenarioDefinition('other', AnotherScenario::class, new AsScenario('alpha'), []);
        $scenarios = ['zebra' => $main, 'alpha' => $other];

        self::assertSame(
            ['alpha' => $other, 'zebra' => $main],
            (new ScenarioSuiteFilter())->filter($scenarios, null),
        );
        self::assertSame(['zebra' => $main, 'alpha' => $other], $scenarios);
    }

    public function testFilterKeepsOnlyMatchingSuiteAndSortsKeys(): void
    {
        $main = new ScenarioDefinition('main', ValidScenario::class, new AsScenario('zebra'), []);
        $other = new ScenarioDefinition('other', AnotherScenario::class, new AsScenario('alpha'), []);

        self::assertSame(
            [ValidScenario::class => $main, 'zebra' => $main],
            (new ScenarioSuiteFilter())->filter([
                'zebra' => $main,
                'alpha' => $other,
                ValidScenario::class => $main,
            ], 'main'),
        );
    }

    public function testFilterReturnsEmptyArrayForUnknownSuite(): void
    {
        $scenario = new ScenarioDefinition('main', ValidScenario::class, new AsScenario('first'), []);

        self::assertSame([], (new ScenarioSuiteFilter())->filter(['first' => $scenario], 'unknown'));
    }

    public function testFilterMatchesSuiteExactly(): void
    {
        $scenario = new ScenarioDefinition('main', ValidScenario::class, new AsScenario('first'), []);
        $filter = new ScenarioSuiteFilter();

        self::assertSame([], $filter->filter(['first' => $scenario], 'Main'));
        self::assertSame([], $filter->filter(['first' => $scenario], ''));
    }
}
