<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\PHPUnit\Subscriber;

use PHPUnit\Event\TestSuite\TestSuite;
use Scenario\Core\Runtime\Metadata\AttributeContext;
use Scenario\Core\Runtime\Metadata\AttributeProcessor;
use Scenario\Core\Runtime\Metadata\ExecutionType;
use Scenario\Core\Runtime\Metadata\Parser\ClassAttributeParser;

abstract class TestClassSubscriber
{
    final protected function doNotify(TestSuite $suite, ExecutionType $executionType): void
    {
        if ($suite->isForTestClass() === true) {
            /** @var class-string $className */
            $className = $suite->name();
            new AttributeProcessor()->process(
                new AttributeContext(
                    $className,
                    null,
                    $executionType,
                ),
                new ClassAttributeParser()->parse($className),
            );
        }
    }
}
