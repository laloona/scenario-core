<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\PHPUnit;

use PHPUnit\Runner\Extension\Facade;
use Scenario\Core\PHPUnit\Subscriber\FailureSubscriber;
use Scenario\Core\PHPUnit\Subscriber\TestClassDownSubscriber;
use Scenario\Core\PHPUnit\Subscriber\TestClassUpSubscriber;
use Scenario\Core\PHPUnit\Subscriber\TestMethodDownSubscriber;
use Scenario\Core\PHPUnit\Subscriber\TestMethodUpSubscriber;

final class SubscriberLoader
{
    public function load(Facade $facade): void
    {
        $facade->registerSubscriber(new FailureSubscriber());

        $facade->registerSubscriber(new TestClassUpSubscriber());
        $facade->registerSubscriber(new TestClassDownSubscriber());

        $facade->registerSubscriber(new TestMethodUpSubscriber());
        $facade->registerSubscriber(new TestMethodDownSubscriber());
    }
}
