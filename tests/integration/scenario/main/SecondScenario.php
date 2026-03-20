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
use Scenario\Core\Attribute\Parameter;
use Scenario\Core\Runtime\Metadata\ParameterType;
use Scenario\Core\Scenario;

#[AsScenario(
    name: 'second-scenario',
    description: 'My second scenario',
)]
#[Parameter(
    name: 'param-1',
    description: 'My first parameter',
    type: ParameterType::Integer,
    required: true,
)]
#[ApplyScenario('first-scenario')]
final class SecondScenario extends Scenario
{
    public function up(): void
    {
        echo 'second scenario was applied with up and parameter ' . $this->parameter('param-1') . PHP_EOL;
    }

    public function down(): void
    {
        echo 'second scenario was applied with down and parameter ' . $this->parameter('param-1') . PHP_EOL;
    }
}
