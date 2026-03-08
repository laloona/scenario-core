<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Contract;

use Scenario\Core\Runtime\ScenarioParameters;

interface ScenarioInterface
{
    public function configure(ScenarioParameters $parameters): void;

    public function up(): void;

    public function down(): void;
}
