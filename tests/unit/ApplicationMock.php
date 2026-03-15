<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Tests\Unit;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use Scenario\Core\Runtime\Application;
use Scenario\Core\Runtime\Application\Configuration\LoadedConfiguration;
use SplFileInfo;
use function is_dir;
use function rmdir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

trait ApplicationMock
{
    private string $rootDir;

    private function createRootDir(): void
    {
        $this->rootDir = sys_get_temp_dir() . '/scenario_core_' . uniqid();
        mkdir($this->rootDir);

        $reflection = new ReflectionClass(Application::class);
        $property = $reflection->getProperty('rootDir');
        $property->setValue(null, $this->rootDir);
    }

    private function removeRootDir(): void
    {
        if (is_dir($this->rootDir) === false) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->rootDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->isDir() === true) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }

        rmdir($this->rootDir);
    }

    private function setConfiguration(LoadedConfiguration $config): void
    {
        $property = new ReflectionClass(Application::class)->getProperty('configuration');
        $property->setValue(null, $config);
    }

    private function resetApplication(): void
    {
        $reflection = new ReflectionClass(Application::class);

        $rootDir = $reflection->getProperty('rootDir');
        $rootDir->setValue(null, null);

        $configuration = $reflection->getProperty('configuration');
        $configuration->setValue(null, null);

        $isBooted = $reflection->getProperty('isBooted');
        $isBooted->setValue(null, false);
    }
}
