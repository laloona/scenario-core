<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Suite\Scenario\Other;

use Stateforge\Scenario\Core\Attribute\AsScenario;
use Stateforge\Scenario\Core\Scenario;
use const PHP_EOL;

#[AsScenario('other-scenario')]
final class OtherScenario extends Scenario
{
    public function up(): void
    {
        echo 'other scenario was applied with up' . PHP_EOL;
    }

    public function down(): void
    {
        echo 'other scenario was applied with down' . PHP_EOL;
    }
}
