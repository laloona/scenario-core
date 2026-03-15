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

use Scenario\Core\Runtime\Application\ApplicationState;
use Scenario\Core\Runtime\Application\Configuration\Configuration;
use Scenario\Core\Runtime\Application\Configuration\ConfigurationBuilder;
use Scenario\Core\Runtime\Application\Configuration\ConfigurationFinder;
use Scenario\Core\Runtime\Application\Configuration\XMLParser;
use Scenario\Core\Runtime\Exception\HandlerRegistryException;
use Scenario\Core\Runtime\Metadata\Handler\ApplyScenarioHandler;
use Scenario\Core\Runtime\Metadata\Handler\RefreshDatabaseHandler;
use Scenario\Core\Runtime\Metadata\HandlerRegistry;
use Throwable;

final class Application
{
    private static ?string $rootDir = null;

    private static ?Configuration $configuration = null;

    private static bool $isBooted = false;

    public function prepare(): void
    {
        if (self::$configuration === null) {
            self::$configuration = (new ConfigurationBuilder(
                new ConfigurationFinder(),
                new XMLParser(
                    self::getRootDir() .DIRECTORY_SEPARATOR .
                    'vendor' . DIRECTORY_SEPARATOR .
                    'scenario' . DIRECTORY_SEPARATOR .
                    'core' . DIRECTORY_SEPARATOR .
                    'xsd' . DIRECTORY_SEPARATOR . 'scenario.xsd',
                ),
            ))->build();

            (new ScenarioLoader(ScenarioRegistry::getInstance()))->loadScenarios(self::$configuration);
        }
    }

    public function bootstrap(): void
    {
        $applicationState = new ApplicationState();

        try {
            $this->prepare();

            if (self::config() !== null
                && is_file(self::getRootDir() . DIRECTORY_SEPARATOR . self::config()->getBootstrap())) {
                include(self::getRootDir() . DIRECTORY_SEPARATOR . self::config()->getBootstrap());
            }
        } catch (Throwable $throwable) {
            $applicationState->fail($throwable);
            return;
        }

        try {
            HandlerRegistry::getInstance()->registerHandler(new RefreshDatabaseHandler());
            HandlerRegistry::getInstance()->registerHandler(new ApplyScenarioHandler(new ScenarioBuilder()));
        } catch (HandlerRegistryException $exception) {
            // default handlers can be overwritten, this exception is ok
        }

        self::$isBooted = true;
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

    public static function isBooted(): bool
    {
        return self::$isBooted;
    }
}
