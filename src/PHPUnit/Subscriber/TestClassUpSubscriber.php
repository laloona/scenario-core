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

use PHPUnit\Event\TestSuite\Started;
use PHPUnit\Event\TestSuite\StartedSubscriber;
use Stateforge\Scenario\Core\Runtime\Metadata\ExecutionType;

final class TestClassUpSubscriber extends TestClassSubscriber implements StartedSubscriber
{
    public function notify(Started $event): void
    {
        $this->doNotify($event->testSuite(), ExecutionType::Up);
    }
}
