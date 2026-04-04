<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\PHPUnit\Finder;

use PHPUnit\Framework\TestCase;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use Stateforge\Scenario\Core\Attribute\ApplyScenario;
use Stateforge\Scenario\Core\Attribute\RefreshDatabase;
use Stateforge\Scenario\Core\PHPUnit\Configuration\ConfigFinder;
use Stateforge\Scenario\Core\Runtime\Application;
use Stateforge\Scenario\Core\Runtime\ClassFinder;
use function array_merge;
use function class_exists;
use const DIRECTORY_SEPARATOR;

final class ScenarioTestFinder
{
    /**
     * @return array<class-string, list<non-empty-string>>
     */
    public function all(): array
    {
        /** @var array<class-string, list<non-empty-string>> $classes */
        $classes = [];
        $directories = (new DirectoryFinder(new ConfigFinder()))->all();
        foreach ($directories as $directory) {
            $classes = array_merge($classes, $this->findTestCLassesUsingScenario(Application::getRootDir() . DIRECTORY_SEPARATOR . $directory));
        }

        return $classes;
    }

    /**
     * @return array<class-string, list<non-empty-string>>
     */
    private function findTestCLassesUsingScenario(string $dirname): array
    {
        /** @var array<class-string, list<non-empty-string>> $testClasses */
        $testClasses = [];
        $candidates = (new ClassFinder())->findClassesInDirectory($dirname);
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
