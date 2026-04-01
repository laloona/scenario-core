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

use Scenario\Core\Attribute\AsScenario;
use Scenario\Core\Scenario;
use const PHP_EOL;

#[AsScenario(
    name: 'first-scenario',
    description: 'My first scenario',
)]
final class FirstScenario extends Scenario
{
    public function up(): void
    {
        echo 'first scenario was applied with up' . PHP_EOL;
    }

    public function down(): void
    {
        echo 'first scenario was applied with down' . PHP_EOL;
    }
}
