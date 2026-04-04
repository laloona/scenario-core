<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class AsScenario
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $description = null,
    ) {
    }
}
