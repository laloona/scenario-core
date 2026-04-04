<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\PHPUnit;

use PHPUnit\Runner\Extension\Facade;
use Stateforge\Scenario\Core\PHPUnit\Subscriber\FailureSubscriber;
use Stateforge\Scenario\Core\PHPUnit\Subscriber\TestClassDownSubscriber;
use Stateforge\Scenario\Core\PHPUnit\Subscriber\TestClassUpSubscriber;
use Stateforge\Scenario\Core\PHPUnit\Subscriber\TestMethodDownSubscriber;
use Stateforge\Scenario\Core\PHPUnit\Subscriber\TestMethodUpSubscriber;

final class SubscriberLoader
{
    public function load(Facade $facade): void
    {
        $facade->registerSubscriber(new FailureSubscriber(new Emitter()));

        $facade->registerSubscriber(new TestClassUpSubscriber());
        $facade->registerSubscriber(new TestClassDownSubscriber());

        $facade->registerSubscriber(new TestMethodUpSubscriber());
        $facade->registerSubscriber(new TestMethodDownSubscriber());
    }
}
