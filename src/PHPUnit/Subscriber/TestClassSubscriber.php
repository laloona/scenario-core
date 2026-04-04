<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\PHPUnit\Subscriber;

use PHPUnit\Event\TestSuite\TestSuite;
use Stateforge\Scenario\Core\Runtime\Metadata\AttributeContext;
use Stateforge\Scenario\Core\Runtime\Metadata\AttributeProcessor;
use Stateforge\Scenario\Core\Runtime\Metadata\ExecutionType;
use Stateforge\Scenario\Core\Runtime\Metadata\Parser\ClassAttributeParser;

abstract class TestClassSubscriber
{
    final protected function doNotify(TestSuite $suite, ExecutionType $executionType): void
    {
        if ($suite->isForTestClass() === true) {
            /** @var class-string $className */
            $className = $suite->name();
            (new AttributeProcessor())->process(
                AttributeContext::getInstance(
                    $className,
                    null,
                    $executionType,
                    false,
                    null,
                ),
                (new ClassAttributeParser())->parse($className),
            );
        }
    }
}
