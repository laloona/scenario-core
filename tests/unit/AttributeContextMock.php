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
use Stateforge\Scenario\Core\Runtime\Metadata\AttributeContext;

trait AttributeContextMock
{
    private function resetAttributeContext(): void
    {
        $reflection = new ReflectionClass(AttributeContext::class);

        $currentClass = $reflection->getProperty('currentClass');
        $currentClass->setValue(null, null);

        $instances = $reflection->getProperty('instances');
        $instances->setValue(null, []);
    }
}
