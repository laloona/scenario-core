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

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use SplFileInfo;
use function array_filter;
use function array_values;
use function get_declared_classes;
use function md5;
use function realpath;
use function str_starts_with;

final class ClassFinder
{
    private string $cacheKey = '';

    /**
     * @return list<class-string>
     */
    public function findClassesInDirectory(string $path): array
    {
        $cacheKey = '';

        $directory = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path),
        );
        foreach ($directory as $file) {
            if (!$file instanceof SplFileInfo) {
                continue;
            }

            if ($file->isFile() === true
                && $file->getExtension() === 'php') {
                $cacheKey .= $file->getMTime();
                include_once($file->getPathname());
            }
        }

        $this->cacheKey = $cacheKey === '' ? '' : md5($cacheKey);

        return $this->filterClassesByDirectory(get_declared_classes(), $path);
    }

    /**
     * @param list<class-string> $classes
     * @return list<class-string>
     */
    private function filterClassesByDirectory(array $classes, string $directory): array
    {
        $directory = realpath($directory);
        if ($directory === false) {
            return [];
        }

        return array_values(array_filter($classes, function ($class) use ($directory) {
            $file = (new ReflectionClass($class))->getFileName();
            return $file !== false
                && str_starts_with($file, $directory);
        }));
    }

    public function getCacheKey(): string
    {
        return $this->cacheKey;
    }
}
