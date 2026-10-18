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

use Stateforge\Scenario\Core\Extension;
use Stateforge\Scenario\Core\Runtime\Application\ApplicationState;
use Stateforge\Scenario\Core\Runtime\Application\Configuration\Configuration;
use Stateforge\Scenario\Core\Runtime\Application\Configuration\ConfigurationBuilder;
use Stateforge\Scenario\Core\Runtime\Application\Configuration\ConfigurationFinder;
use Stateforge\Scenario\Core\Runtime\Application\Configuration\XMLParser;
use Stateforge\Scenario\Core\Runtime\Exception\HandlerRegistryException;
use Stateforge\Scenario\Core\Runtime\Exception\InvalidExtensionSubClassException;
use Stateforge\Scenario\Core\Runtime\Metadata\Handler\ApplyScenarioHandler;
use Stateforge\Scenario\Core\Runtime\Metadata\Handler\RefreshDatabaseHandler;
use Stateforge\Scenario\Core\Runtime\Metadata\HandlerRegistry;
use Stateforge\Scenario\Core\Runtime\Metadata\Parameter\ParameterTypeLoader;
use Stateforge\Scenario\Core\Runtime\Metadata\Parameter\ParameterTypeRegistry;
use Throwable;
use function array_search;
use function array_slice;
use function explode;
use function get_class;
use function implode;
use function is_file;
use function is_int;
use function is_subclass_of;
use const DIRECTORY_SEPARATOR;

final class Application
{
    private static ?string $rootDir = null;

    private static ?Configuration $configuration = null;

    /**
     * @var list<ApplicationExtension>
     */
    private array $extensions = [];

    public static function getRootDir(): string
    {
        if (self::$rootDir === null) {
            $dirs = explode(DIRECTORY_SEPARATOR, __DIR__);
            $vendor = array_search('vendor', $dirs, true);
            if ($vendor === false
                || is_int($vendor) === false) {
                $vendor = -2;
            }

            self::$rootDir = implode(DIRECTORY_SEPARATOR, array_slice($dirs, 0, $vendor));
        }

        return self::$rootDir;
    }

    public static function config(): ?Configuration
    {
        return self::$configuration;
    }

    private function getXsd(): string
    {
        $vendorPath = self::getRootDir() . DIRECTORY_SEPARATOR .
            'vendor' . DIRECTORY_SEPARATOR .
            'stateforge' . DIRECTORY_SEPARATOR .
            'scenario-core' . DIRECTORY_SEPARATOR .
            'xsd' . DIRECTORY_SEPARATOR . 'scenario.xsd';

        if (is_file($vendorPath) === true) {
            return $vendorPath;
        }

        return self::getRootDir() . DIRECTORY_SEPARATOR .
            'xsd' . DIRECTORY_SEPARATOR . 'scenario.xsd';
    }

    public function prepare(): void
    {
        if (self::$configuration !== null) {
            return;
        }

        self::$configuration = (new ConfigurationBuilder(
            new ConfigurationFinder(),
            new XMLParser($this->getXsd()),
        ))->build();

        foreach (self::$configuration->getExtensions() as $extension) {
            if (is_subclass_of($extension, Extension::class) === false) {
                throw new InvalidExtensionSubClassException(get_class($extension));
            }

            $applicationExtension = $extension->application($this);
            if ($applicationExtension !== null) {
                $this->extensions[] = $applicationExtension;
            }
        }

        foreach ($this->extensions as $extension) {
            $extension->prepare();
        }

        (new ParameterTypeLoader(ParameterTypeRegistry::getInstance()))->loadTypes(self::$configuration);
        (new ScenarioLoader(
            ScenarioRegistry::getInstance(),
            ParameterTypeRegistry::getInstance(),
        ))->loadScenarios(self::$configuration);
    }

    public function bootstrap(): void
    {
        $applicationState = new ApplicationState();

        try {
            $this->prepare();
            foreach ($this->extensions as $extension) {
                $extension->boot();
            }
        } catch (Throwable $throwable) {
            $applicationState->fail($throwable);
            return;
        }

        try {
            HandlerRegistry::getInstance()->registerHandler(new RefreshDatabaseHandler(new DatabaseRefreshExecutor()));
            HandlerRegistry::getInstance()->registerHandler(new ApplyScenarioHandler(new ScenarioBuilder()));
        } catch (HandlerRegistryException $exception) {
            // default handlers can be overwritten, this exception is ok
        }
    }
}
