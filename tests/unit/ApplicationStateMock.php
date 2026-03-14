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

use ReflectionClass;
use Scenario\Core\Runtime\Application\ApplicationState;

trait ApplicationStateMock
{
    private function resetApplicationState(): void
    {
        $state = new ReflectionClass(ApplicationState::class);

        $throwable = $state->getProperty('throwable');
        $throwable->setValue(null, null);

        $classes = $state->getProperty('classes');
        $classes->setValue(null, []);
    }
}
