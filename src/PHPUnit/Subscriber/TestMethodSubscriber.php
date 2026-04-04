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

use PHPUnit\Event\Code\Test;
use PHPUnit\Event\Code\TestMethod;
use Stateforge\Scenario\Core\Runtime\Metadata\AttributeContext;
use Stateforge\Scenario\Core\Runtime\Metadata\AttributeProcessor;
use Stateforge\Scenario\Core\Runtime\Metadata\ExecutionType;
use Stateforge\Scenario\Core\Runtime\Metadata\Parser\MethodAttributeParser;

abstract class TestMethodSubscriber
{
    final protected function doNotify(Test $test, ExecutionType $executionType): void
    {
        if ($test instanceof TestMethod) {
            (new AttributeProcessor())->process(
                AttributeContext::getInstance(
                    $test->className(),
                    $test->methodName(),
                    $executionType,
                    false,
                    null,
                ),
                (new MethodAttributeParser())->parse($test->className(), $test->methodName()),
            );
        }
    }
}
