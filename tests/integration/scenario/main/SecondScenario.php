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
use Stateforge\Scenario\Core\Attribute\Parameter;
use Stateforge\Scenario\Core\ParameterType;
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
#[ApplyScenario('first-scenario', [ 'myint' => 4 ])]
final class SecondScenario extends Scenario
{
    public function up(): void
    {
        $param = is_int($this->parameter('param-1'))
            ? $this->parameter('param-1')
            : '';

        echo 'second scenario was applied with up and parameter ' . $param . PHP_EOL;
    }

    public function down(): void
    {
        $param = is_int($this->parameter('param-1'))
            ? $this->parameter('param-1')
            : '';

        echo 'second scenario was applied with down and parameter ' . $param . PHP_EOL;
    }
}
