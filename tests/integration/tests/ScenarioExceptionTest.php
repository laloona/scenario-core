<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\ScenarioTests;

use PHPUnit\Framework\TestCase;
use Stateforge\Scenario\Core\Attribute\ApplyScenario;

final class ScenarioExceptionTest extends TestCase
{
    #[ApplyScenario('second-scenario', [ 'param-1' => 5 ])]
    #[ApplyScenario('failed-scenario')]
    public function testScenario(): void
    {
        self::expectNotToPerformAssertions();
    }
}
