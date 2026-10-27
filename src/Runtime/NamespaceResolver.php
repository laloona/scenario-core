<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Runtime;

use function array_map;
use function explode;
use function implode;
use function str_replace;
use function ucfirst;

final class NamespaceResolver
{
    public function toNamespace(string $directory): string
    {
        return implode('\\', array_map(function (string $part): string {
            return ucfirst($part);
        }, explode('/', str_replace('\\', '/', $directory))));
    }
}
