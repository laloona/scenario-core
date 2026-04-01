<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Main;

use Scenario\Core\Attribute\ApplyScenario;
use Scenario\Core\Attribute\AsScenario;
use Scenario\Core\Scenario;
use const PHP_EOL;

#[AsScenario(
    name: 'third-scenario',
    description: 'My third scenario',
)]
final class ThirdScenario extends Scenario
{
    #[ApplyScenario('first-scenario')]
    public function up(): void
    {
        echo 'third scenario was applied with up' . PHP_EOL;
    }

    #[ApplyScenario('second-scenario', [ 'param-1' => 5 ])]
    public function down(): void
    {
        echo 'third scenario was applied with down' . PHP_EOL;
    }
}
