<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Tests\Files;

use Scenario\Core\Attribute\ApplyScenario;
use Scenario\Core\Attribute\AsScenario;
use Scenario\Core\Contract\ScenarioInterface;
use Scenario\Core\Runtime\ScenarioParameters;

#[AsScenario]
final class ValidScenario implements ScenarioInterface
{
    public function configure(ScenarioParameters $parameters): void
    {
    }

    #[ApplyScenario('my-scenario')]
    public function up(): void
    {
    }

    public function down(): void
    {
    }
}
