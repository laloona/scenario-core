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

use PHPUnit\Event\TestSuite\Finished;
use PHPUnit\Event\TestSuite\FinishedSubscriber;
use Stateforge\Scenario\Core\Runtime\Metadata\ExecutionType;

final class TestClassDownSubscriber extends TestClassSubscriber implements FinishedSubscriber
{
    public function notify(Finished $event): void
    {
        $this->doNotify($event->testSuite(), ExecutionType::Down);
    }
}
