<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Other;

use Exception;
use Scenario\Core\Attribute\AsScenario;
use Scenario\Core\Scenario;

#[AsScenario('failed-scenario')]
final class FailedScenario extends Scenario
{
    public function up(): void
    {
        throw new Exception('some error happend in up.');
    }

    public function down(): void
    {
        throw new Exception('some error happend in down.');
    }
}
