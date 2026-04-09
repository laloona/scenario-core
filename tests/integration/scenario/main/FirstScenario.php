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

use Stateforge\Scenario\Core\Attribute\AsScenario;
use Stateforge\Scenario\Core\Attribute\Parameter;
use Stateforge\Scenario\Core\Scenario;
use Stateforge\Suite\Scenario\Parameter\IntegerParameterType;
use function is_int;
use const PHP_EOL;

#[AsScenario(
    name: 'first-scenario',
    description: 'My first scenario',
)]
#[Parameter(
    name: 'myint',
    type: IntegerParameterType::class,
    default: 1,
)]
final class FirstScenario extends Scenario
{
    public function up(): void
    {
        $param = is_int($this->parameter('myint'))
            ? $this->parameter('myint')
            : '';

        echo 'first scenario was applied with up and custom parameter ' . $param . PHP_EOL;
    }

    public function down(): void
    {
        $param = is_int($this->parameter('myint'))
            ? $this->parameter('myint')
            : '';

        echo 'first scenario was applied with down and custom parameter ' . $param . PHP_EOL;
    }
}
