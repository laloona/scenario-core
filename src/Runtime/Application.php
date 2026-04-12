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

use Stateforge\Scenario\Core\Runtime\Application\ApplicationState;
use Stateforge\Scenario\Core\Runtime\Application\Configuration\Configuration;
use Stateforge\Scenario\Core\Runtime\Application\Configuration\ConfigurationBuilder;
use Stateforge\Scenario\Core\Runtime\Application\Configuration\ConfigurationFinder;
use Stateforge\Scenario\Core\Runtime\Application\Configuration\XMLParser;
use Stateforge\Scenario\Core\Runtime\Exception\HandlerRegistryException;
use Stateforge\Scenario\Core\Runtime\Metadata\Handler\ApplyScenarioHandler;
use Stateforge\Scenario\Core\Runtime\Metadata\Handler\RefreshDatabaseHandler;
use Stateforge\Scenario\Core\Runtime\Metadata\HandlerRegistry;
use Stateforge\Scenario\Core\Runtime\Metadata\Parameter\ParameterTypeLoader;
use Stateforge\Scenario\Core\Runtime\Metadata\Parameter\ParameterTypeRegistry;
use Throwable;
use function array_search;
use function array_slice;
use function explode;
use function implode;
use function is_file;
use function is_int;
use const DIRECTORY_SEPARATOR;

final class Application
{
    private static ?ApplicationExtension $extension = null;

    private static ?string $rootDir = null;

    private static ?Configuration $configuration = null;

    public static function extend(ApplicationExtension $application): void
    {
        self::$extension = $application;
    }

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

        $bootstrapFile = self::getRootDir() . DIRECTORY_SEPARATOR . self::$configuration->getBootstrap();
        if (is_file($bootstrapFile)) {
            include($bootstrapFile);
        }

        if (self::$extension !== null) {
            self::$extension->prepare();
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
            if (self::$extension !== null) {
                self::$extension->boot();
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
