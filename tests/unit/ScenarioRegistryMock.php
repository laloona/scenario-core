<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Tests\Unit;

use ReflectionClass;
use Stateforge\Scenario\Core\Runtime\ScenarioRegistry;

trait ScenarioRegistryMock
{
    private function resetScenarioRegistry(): void
    {
        $property = new ReflectionClass(ScenarioRegistry::class)->getProperty('instance');
        $property->setValue(null, null);
    }
}
