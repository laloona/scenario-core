<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Runtime\Application;

use DirectoryIterator;
use function is_dir;
use function mkdir;
use function unlink;

final class CacheDirectory
{
    public function prepare(string $directory): void
    {
        if (is_dir($directory) === false) {
            mkdir($directory, 0777, true);
        } else {
            $directory = new DirectoryIterator($directory);
            foreach ($directory as $file) {
                if ($file->isFile() === true) {
                    $path = $file->getRealPath();
                    if ($path !== false) {
                        unlink($path);
                    }
                }
            }
        }
    }
}
