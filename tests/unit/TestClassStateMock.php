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
use Scenario\Core\Runtime\Application\TestClassState;

trait TestClassStateMock
{
    private function resetClassMethodState(): void
    {
        $throwables = new ReflectionClass(TestClassState::class)->getProperty('throwables');
        $throwables->setValue(null, []);
    }
}
