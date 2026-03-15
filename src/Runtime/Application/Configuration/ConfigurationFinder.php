<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Runtime\Application\Configuration;

use DirectoryIterator;
use Scenario\Core\Runtime\Application;
use SplFileInfo;
use UnexpectedValueException;

final class ConfigurationFinder
{
    public function find(): ?SplFileInfo
    {
        try {
            $directory = new DirectoryIterator(Application::getRootDir());
            foreach ($directory as $file) {
                if ($file->isFile()
                    && in_array($file->getFilename(), [ 'scenario.dist.xml', 'scenario.xml' ], true)) {
                    return $file;
                }
            }
        } catch (UnexpectedValueException $exception) {
            // in this case we can use the fallback default configuration
        }

        return null;
    }
}
