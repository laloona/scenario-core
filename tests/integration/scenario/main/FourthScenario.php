<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Suite\Scenario\Main;

use Stateforge\Scenario\Core\Attribute\ApplyScenario;
use Stateforge\Scenario\Core\Attribute\AsScenario;
use Stateforge\Scenario\Core\Scenario;
use const PHP_EOL;

#[AsScenario(
    name: 'fourth-scenario',
    description: 'My fourth scenario',
)]
final class FourthScenario extends Scenario
{
    #[ApplyScenario('third-scenario')]
    #[ApplyScenario('first-scenario', [ 'myint' => 9 ])]
    public function up(): void
    {
        echo 'fourth scenario was applied with up' . PHP_EOL;
    }

    public function down(): void
    {
        echo 'fourth scenario was applied with down' . PHP_EOL;
    }
}
