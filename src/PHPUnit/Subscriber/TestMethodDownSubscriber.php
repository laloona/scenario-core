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

use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\FinishedSubscriber;
use Scenario\Core\Runtime\Metadata\ExecutionType;

final class TestMethodDownSubscriber extends TestMethodSubscriber implements FinishedSubscriber
{
    public function notify(Finished $event): void
    {
        $this->doNotify($event->test(), ExecutionType::Down);
    }
}
