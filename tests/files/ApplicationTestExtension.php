<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Tests\Files;

use RuntimeException;
use Stateforge\Scenario\Core\Console\CliApplication;
use Stateforge\Scenario\Core\Console\CliApplicationExtension;
use Stateforge\Scenario\Core\Extension;
use Stateforge\Scenario\Core\Runtime\Application;
use Stateforge\Scenario\Core\Runtime\ApplicationExtension;

final class ApplicationTestExtension implements Extension, ApplicationExtension
{
    /** @var list<string> */
    public static array $calls = [];

    public static bool $throwOnBoot = false;

    public function application(Application $application): ApplicationExtension
    {
        return $this;
    }

    public function cli(CliApplication $cli): ?CliApplicationExtension
    {
        return null;
    }

    public function prepare(): void
    {
        self::$calls[] = 'prepare';
    }

    public function boot(): void
    {
        self::$calls[] = 'boot';
        if (self::$throwOnBoot === true) {
            throw new RuntimeException('something went wrong');
        }
    }
}
