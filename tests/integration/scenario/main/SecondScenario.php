<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Main;

use Stateforge\Scenario\Core\Attribute\ApplyScenario;
use Stateforge\Scenario\Core\Attribute\AsScenario;
use Stateforge\Scenario\Core\Attribute\Parameter;
use Stateforge\Scenario\Core\Runtime\Metadata\ParameterType;
use Stateforge\Scenario\Core\Scenario;
use function is_int;
use const PHP_EOL;

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
        $param = '';
        if (is_int($this->parameter('param-1'))) {
            $param = $this->parameter('param-1');
        }
        echo 'second scenario was applied with up and parameter ' . $param . PHP_EOL;
    }

    public function down(): void
    {
        $param = '';
        if (is_int($this->parameter('param-1'))) {
            $param = $this->parameter('param-1');
        }
        echo 'second scenario was applied with down and parameter ' . $param . PHP_EOL;
    }
}
