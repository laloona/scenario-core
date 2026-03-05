<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\PHPUnit\Finder;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use Scenario\Core\Attribute\ApplyScenario;
use Scenario\Core\Attribute\RefreshDatabase;
use SplFileInfo;

final class ScenarioTestFinder
{
    /**
     * @return array<class-string, list<non-empty-string>>
     */
    public function all(): array
    {
        /** @var array<class-string, list<non-empty-string>> $classes */
        $classes = [];
        $directories = (new DirectoryFinder())->all();
        foreach ($directories as $directory) {
            $classes = array_merge($classes, $this->findTestCLassesUsingScenario($directory));
        }

        return $classes;
    }

    /**
     * @return array<class-string, list<non-empty-string>>
     */
    private function findTestCLassesUsingScenario(string $dirname): array
    {
        $current = get_declared_classes();

        $directory = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dirname),
        );
        foreach ($directory as $file) {
            if (!$file instanceof SplFileInfo) {
                continue;
            }

            if ($file->isFile() === true
                && $file->getExtension() === 'php') {
                include_once($file->getPathname());
            }
        }

        /** @var array<class-string, list<non-empty-string>> $testClasses */
        $testClasses = [];
        $candidates = array_diff(get_declared_classes(), $current);
        foreach ($candidates as $className) {
            if (class_exists($className) === false) {
                continue;
            }

            $reflectionCLass = new ReflectionClass($className);
            if ($reflectionCLass->isSubclassOf(TestCase::class) === false
                || $reflectionCLass->isAbstract() === true
                || $reflectionCLass->isInstantiable() === false) {
                continue;
            }

            if ($this->containsScenarioAttribute($reflectionCLass->getAttributes()) === true
                && isset($testClasses[$className]) === false) {
                $testClasses[$className] = [];
            }

            $methods = $reflectionCLass->getMethods(ReflectionMethod::IS_PUBLIC);
            foreach ($methods as $method) {
                if ($method->class !== $className) {
                    continue;
                }

                if ($this->containsScenarioAttribute($method->getAttributes()) === true) {
                    if (isset($testClasses[$className]) === false) {
                        $testClasses[$className] = [];
                    }
                    $testClasses[$className][] = $method->getName();
                    break;
                }
            }
        }

        return $testClasses;
    }

    /**
     * @param list<ReflectionAttribute<object>> $attributes
     */
    private function containsScenarioAttribute(array $attributes): bool
    {
        foreach ($attributes as $attribute) {
            if ($attribute->getName() === RefreshDatabase::class
                || $attribute->getName() === ApplyScenario::class) {
                return true;
            }
        }

        return false;
    }
}
