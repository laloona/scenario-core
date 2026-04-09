<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Runtime\Metadata\Parameter;

use ReflectionClass;
use Stateforge\Scenario\Core\Runtime\Application;
use Stateforge\Scenario\Core\Runtime\Application\CacheDirectory;
use Stateforge\Scenario\Core\Runtime\Application\Configuration\Configuration;
use Stateforge\Scenario\Core\Runtime\ClassFinder;
use Throwable;
use function count;
use function dirname;
use function file_get_contents;
use function file_put_contents;
use function is_array;
use function is_file;
use function is_string;
use function json_decode;
use function json_encode;
use function realpath;
use function unlink;
use const DIRECTORY_SEPARATOR;

final class ParameterTypeLoader
{
    private string $cacheKey;

    public function __construct(private ParameterTypeRegistry $registry)
    {
    }

    public function loadTypes(Configuration $configuration): void
    {
        $types = $this->readTypes($configuration);
        $cacheFile = $configuration->getCacheDirectory() . DIRECTORY_SEPARATOR . 'parameter' . DIRECTORY_SEPARATOR;

        // try to load from cache
        try {
            if ($this->cacheKey !== '') {
                $cacheFile .= $configuration->getCacheKey() . $this->cacheKey;
                if ($this->fromCache($cacheFile) === true) {
                    return;
                }
            }
        } catch (Throwable $exception) {
            if (is_file($cacheFile) === true) {
                unlink($cacheFile);
            }
        }

        $cachedTypes = $this->registerTypes($types);
        if ($this->cacheKey !== ''
            && count($cachedTypes) > 0) {
            $this->buildCache($cacheFile, $cachedTypes);
        }
    }

    private function fromCache(string $cacheFile): bool
    {
        if (is_file($cacheFile) === false) {
            return false;
        }

        $content = file_get_contents($cacheFile);
        if ($content === false) {
            return false;
        }

        $cachedTypes = json_decode($content, true);
        if (is_array($cachedTypes) === false) {
            return false;
        }

        foreach ($cachedTypes as $definition) {
            if (is_string($definition) === false) {
                continue;
            }

            /** @var class-string $definition */
            $this->registry->register($definition);
        }

        return true;
    }

    /**
     * @param list<class-string> $cachesTypes
     */
    private function buildCache(string $cacheFile, array $cachesTypes): void
    {
        (new CacheDirectory())->prepare(dirname($cacheFile));
        file_put_contents($cacheFile, json_encode($cachesTypes));
    }

    /**
     * @param list<class-string> $types
     * @return list<class-string>
     */
    private function registerTypes(array $types): array
    {
        $cachedTypes = [];
        foreach ($types as $class) {
            $reflection = new ReflectionClass($class);
            if ($reflection->isAbstract() === true
                || $reflection->isInstantiable() === false) {
                continue;
            }

            $this->registry->register($class);
            $cachedTypes[] = $class;
        }

        return $cachedTypes;
    }

    /**
     * @return list<class-string>
     */
    private function readTypes(Configuration $configuration): array
    {
        $this->cacheKey = '';
        $path = realpath(Application::getRootDir() . DIRECTORY_SEPARATOR . $configuration->getParameterDirectory());
        if ($path === false) {
            return [];
        }

        $classFinder = new ClassFinder();
        $types = $classFinder->findClassesInDirectory($path);
        $this->cacheKey = $classFinder->getCacheKey();

        return $types;
    }
}
