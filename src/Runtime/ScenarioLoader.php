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
use ReflectionClass;
use Scenario\Core\Attribute\AsScenario;
use Scenario\Core\Attribute\Parameter;
use Scenario\Core\Runtime\Application\Configuration\Configuration;
use Scenario\Core\Runtime\Exception\ScenarioLoaderException;
use Scenario\Core\Runtime\Metadata\ParameterType;
use Throwable;
use UnexpectedValueException;
use ValueError;
use function assert;
use function class_exists;
use function count;
use function dirname;
use function file_get_contents;
use function file_put_contents;
use function is_array;
use function is_bool;
use function is_dir;
use function is_file;
use function is_string;
use function json_decode;
use function json_encode;
use function mkdir;
use function realpath;
use function sprintf;
use function unlink;
use const DIRECTORY_SEPARATOR;

final class ScenarioLoader
{
    private string $cacheKey;

    public function __construct(private ScenarioRegistry $registry)
    {
    }

    public function loadScenarios(Configuration $configuration): void
    {
        $this->registry->clear();
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
        if ($this->cacheKey !== ''
            && count($cachedSuites) > 0) {
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
                    $parameters = $definition['parameters'] ?? [];

                    if (is_string($class) === false
                        || $class === ''
                        || class_exists($class) === false
                        || !(is_string($name) === true || $name === null)
                        || !(is_string($description) === true || $description === null)
                        || is_array($parameters) === false) {
                        continue;
                    }

                    /** @var list<array{name?: mixed, description?: mixed, required?: mixed, default?: mixed}> $parameters */
                    $parameterInstances = [];
                    foreach ($parameters as $parameter) {
                        $parameterName = $parameter['name'] ?? null;
                        $parameterType = $parameter['type'] ?? null;
                        $parameterDescription = $parameter['description'] ?? null;
                        $parameterRequired = $parameter['required'] ?? null;
                        $parameterRepeatable = $parameter['repeatable'] ?? null;
                        $parameterDefault = $parameter['default'] ?? null;

                        if (is_string($parameterName) === false
                            || is_string($parameterType) === false
                            || !(is_string($parameterDescription) === true || $parameterDescription === null)
                            || is_bool($parameterRequired) === false
                            || is_bool($parameterRepeatable) === false) {
                            continue;
                        }

                        $parameterType = ParameterType::tryFrom($parameterType);
                        if ($parameterType === null) {
                            continue;
                        }

                        $parameterInstances[] = new Parameter($parameterName, $parameterType, $parameterDescription, $parameterRequired, $parameterRepeatable, $parameterDefault);
                    }

                    $this->registry->register(
                        new ScenarioDefinition(
                            $suite,
                            $class,
                            new AsScenario($name, $description),
                            $parameterInstances,
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
                if ($reflection->isAbstract() === true
                    || $reflection->isInstantiable() === false) {
                    continue;
                }

                $attributes = $reflection->getAttributes(AsScenario::class);
                foreach ($attributes as $attribute) {
                    $attributeInstance = $attribute->newInstance();

                    assert($attributeInstance instanceof AsScenario);

                    $parameters = [];
                    $cacheParameters = [];
                    $parameterAttributes = $reflection->getAttributes(Parameter::class);
                    foreach ($parameterAttributes as $parameterAttribute) {
                        $parameterInstance = $parameterAttribute->newInstance();

                        assert($parameterInstance instanceof Parameter);

                        $parameters[] = $parameterInstance;
                        $cacheParameters[] = [
                            'name' => $parameterInstance->name,
                            'type' => $parameterInstance->type->value,
                            'description' => $parameterInstance->description,
                            'required' => $parameterInstance->required,
                            'repeatable' => $parameterInstance->repeatable,
                            'default' => $parameterInstance->default,
                        ];
                    }

                    $this->registry->register(new ScenarioDefinition($suite, $class, $attributeInstance, $parameters));

                    $cachedSuites[$suite][] = [
                        'name' => (string) $attributeInstance->name,
                        'description' => (string) $attributeInstance->description,
                        'class' => (string) $class,
                        'parameters' => $cacheParameters,
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
        $this->cacheKey = '';
        $classFinder = new ClassFinder();
        $suites = [];
        foreach ($configuration->getSuites() as $suite) {
            try {
                $path = realpath(Application::getRootDir() . DIRECTORY_SEPARATOR . $suite->directory);
                if ($path === false) {
                    throw new UnexpectedValueException(sprintf('directory "%s" doesn\'t exist', $suite->directory));
                }

                $suites[$suite->name] = $classFinder->findClassesInDirectory($path);
            } catch (UnexpectedValueException|ValueError $exception) {
                throw new ScenarioLoaderException($suite->directory, $exception);
            }

            $this->cacheKey .= $classFinder->getCacheKey();
        }

        return $suites;
    }
}
