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

use Stateforge\Scenario\Core\Console\CliApplication;
use Stateforge\Scenario\Core\Console\CliApplicationExtension;
use Stateforge\Scenario\Core\Extension;
use Stateforge\Scenario\Core\Runtime\Application;
use Stateforge\Scenario\Core\Runtime\ApplicationExtension;

final class OtherExtension implements Extension
{
    public function application(Application $application): ?ApplicationExtension
    {
        return null;
    }

    public function cli(CliApplication $cli): ?CliApplicationExtension
    {
        return null;
    }
}
