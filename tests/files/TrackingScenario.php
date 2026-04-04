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

use Stateforge\Scenario\Core\Attribute\AsScenario;
use Stateforge\Scenario\Core\Contract\ScenarioInterface;
use Stateforge\Scenario\Core\Runtime\ScenarioParameters;

#[AsScenario('tracking')]
final class TrackingScenario implements ScenarioInterface
{
    public static int $upCount = 0;
    public static int $downCount = 0;
    /** @var array<string, mixed>|null */
    public static ?array $configuredParameters = null;

    public function configure(ScenarioParameters $parameters): void
    {
        self::$configuredParameters = $parameters->all();
    }

    public function up(): void
    {
        self::$upCount++;
    }

    public function down(): void
    {
        self::$downCount++;
    }
}
