<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Runtime;

use DirectoryIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use Scenario\Core\Application;
use Scenario\Core\Attribute\AsScenario;
use Scenario\Core\Runtime\Application\Configuration\Configuration;
use Scenario\Core\Runtime\Exception\ScenarioLoaderException;
use SplFileInfo;
use Throwable;
use UnexpectedValueException;
use ValueError;
use function array_diff;
use function array_values;
use function dirname;
use function file_get_contents;
use function file_put_contents;
use function get_declared_classes;
use function is_dir;
use function is_file;
use function json_decode;
use function json_encode;
use function mkdir;
use function unlink;

final class ScenarioLoader
{
    private string $cacheKey;

    public function __construct(private ScenarioRegistry $registry)
    {
    }

    public function loadScenarios(Configuration $configuration): void
    {
        $suites = $this->readSuites($configuration);
        $configuration->setCacheKey($this->cacheKey);
        $cacheFile = $configuration->getCacheDirectory() . DIRECTORY_SEPARATOR;

        // try to load from cache
        try {
            if ($this->cacheKey !== '') {
                $cacheFile .= $configuration->getCacheKey();
                if ($this->fromCache($cacheFile) === true) {
                    return;
                }
            }
        } catch (Throwable $exception) {
            $this->registry->clear();
            if (is_file($cacheFile) === true) {
                unlink($cacheFile);
            }
        }

        $cachedSuites = $this->loadSuites($suites);
        if (count($cachedSuites) > 0) {
            $this->buildCache($cacheFile, $cachedSuites);
        }
    }

    private function fromCache(string $cacheFile): bool
    {
        if (is_file($cacheFile)) {
            $content = file_get_contents($cacheFile);
            if ($content === false) {
                return false;
            }

            $cachedSuites = json_decode($content, true);
            if (!is_array($cachedSuites)) {
                return false;
            }

            foreach ($cachedSuites as $suite => $definitions) {
                if (is_string($suite) === false
                    || is_array($definitions) === false) {
                    continue;
                }

                foreach ($definitions as $definition) {
                    if (is_array($definition) === false) {
                        continue;
                    }

                    $class = $definition['class'] ?? null;
                    $name = $definition['name'] ?? null;
                    $description = $definition['description'] ?? null;

                    if (is_string($class) === false
                        || !(is_string($name) === true || $name === null)
                        || !(is_string($description) === true || $description === null)) {
                        continue;
                    }

                    if ($class === ''
                        || class_exists($class) === false) {
                        continue;
                    }

                    $this->registry->register(
                        new ScenarioDefinition(
                            $suite,
                            new AsScenario($name, $description),
                            $class,
                        ),
                    );
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @param array<string, list<array{name: string, description: string, class: class-string}>> $cachedSuites
     */
    private function buildCache(string $cacheFile, array $cachedSuites): void
    {
        if (is_dir(dirname($cacheFile)) === false) {
            mkdir(dirname($cacheFile));
        } else {
            $directory = new DirectoryIterator(dirname($cacheFile));
            foreach ($directory as $file) {
                if ($file->isFile() === true) {
                    $path = $file->getRealPath();
                    if ($path !== false) {
                        unlink($path);
                    }
                }
            }
        }

        file_put_contents($cacheFile, json_encode($cachedSuites));
    }

    /**
     * @param array<string, list<class-string>> $suites
     * @return array<string, list<array{name: string, description: string, class: class-string}>>
     */
    private function loadSuites(array $suites): array
    {
        $cachedSuites = [];
        foreach ($suites as $suite => $classes) {
            $cachedSuites[$suite] = [];
            foreach ($classes as $class) {
                $reflection = new ReflectionClass($class);
                if ($reflection->isAbstract() === true) {
                    continue;
                }

                $attributes = $reflection->getAttributes(AsScenario::class);
                foreach ($attributes as $attribute) {
                    $attributeInstance = $attribute->newInstance();

                    assert($attributeInstance instanceof AsScenario);

                    $this->registry->register(new ScenarioDefinition($suite, $attributeInstance, $class));
                    $cachedSuites[$suite][] = [
                        'name' => (string) $attributeInstance->name,
                        'description' => (string) $attributeInstance->description,
                        'class' => (string) $class,
                    ];
                }
            }
        }

        return $cachedSuites;
    }

    /**
     * @return array<string, list<class-string>>
     * @throws ScenarioLoaderException
     */
    private function readSuites(Configuration $configuration): array
    {
        $cacheKey = '';
        $suites = [];
        foreach ($configuration->getSuites() as $suite) {
            try {
                $path = realpath(Application::getRootDir() . DIRECTORY_SEPARATOR . $suite->directory);
                if ($path === false) {
                    throw new UnexpectedValueException(sprintf('directory "%s" doesn\'t exist', $suite->directory));
                }

                $declaredClasses = get_declared_classes();
                $directory = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($path),
                );
                foreach ($directory as $file) {
                    if (!$file instanceof SplFileInfo) {
                        continue;
                    }

                    if ($file->isFile() === true
                        && $file->getExtension() === 'php') {
                        $cacheKey .= $file->getMTime() . $suite->name;
                        include_once($file->getPathname());
                    }
                }
                $suites[$suite->name] = array_values(array_diff(get_declared_classes(), $declaredClasses));
            } catch (UnexpectedValueException|ValueError $exception) {
                throw new ScenarioLoaderException($suite->directory, $exception);
            }
        }

        $this->cacheKey = md5($cacheKey);
        return $suites;
    }
}
