<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\PHPUnit\Configuration;

use Scenario\Core\Runtime\Application;

final class ConfigFinder
{
    public function find(): ?string
    {
        $files = [ 'phpunit.dist.xml', 'phpunit.xml' ];
        foreach ($files as $file) {
            if (is_file(Application::getRootDir() . DIRECTORY_SEPARATOR . $file)) {
                return Application::getRootDir() . DIRECTORY_SEPARATOR . $file;
            }
        }

        return null;
    }
}
