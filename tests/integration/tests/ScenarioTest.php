<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ScenarioTests;

use PHPUnit\Framework\TestCase;
use Scenario\Core\Attribute\ApplyScenario;

final class ScenarioTest extends TestCase
{
    #[ApplyScenario('first-scenario')]
    #[ApplyScenario('other-scenario')]
    public function testScenario(): void
    {
        self::expectNotToPerformAssertions();
    }
}
