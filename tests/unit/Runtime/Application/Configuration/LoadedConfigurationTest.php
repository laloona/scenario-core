<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Tests\Unit\Runtime\Application\Configuration;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Scenario\Core\Application;
use Scenario\Core\Runtime\Application\Configuration\DefaultConfiguration;
use Scenario\Core\Runtime\Application\Configuration\LoadedConfiguration;
use Scenario\Core\Runtime\Application\Configuration\Value\ConnectionValue;
use Scenario\Core\Runtime\Application\Configuration\Value\SuiteValue;

#[CoversClass(LoadedConfiguration::class)]
#[UsesClass(DefaultConfiguration::class)]
#[UsesClass(SuiteValue::class)]
#[UsesClass(ConnectionValue::class)]
#[UsesClass(Application::class)]
#[Group('runtime')]
final class LoadedConfigurationTest extends TestCase
{
    protected function setUp(): void
    {
        $reflection = new ReflectionClass(Application::class);
        $property = $reflection->getProperty('rootDir');
        $property->setValue(null, 'root');
    }

    protected function tearDown(): void
    {
        $reflection = new ReflectionClass(Application::class);
        $property = $reflection->getProperty('rootDir');
        $property->setValue(null, null);
    }

    public function testUsesDefaultBootstrapWhenNotSet(): void
    {
        $default = new DefaultConfiguration();
        $config = new LoadedConfiguration($default);

        self::assertSame($default->getBootstrap(), $config->getBootstrap());
    }

    public function testOverridesBootstrap(): void
    {
        $default = new DefaultConfiguration();
        $config = new LoadedConfiguration($default);

        $config->setBootstrap('bootstrap.php');

        self::assertSame('bootstrap.php', $config->getBootstrap());
    }

    public function testUsesDefaultCacheDirectory(): void
    {
        $default = new DefaultConfiguration();
        $config = new LoadedConfiguration($default);

        self::assertSame($default->getCacheDirectory(), $config->getCacheDirectory());
    }

    public function testOverridesCacheDirectory(): void
    {
        $default = new DefaultConfiguration();
        $config = new LoadedConfiguration($default);

        $config->setCacheDirectory('/mycache');

        self::assertSame('/mycache', $config->getCacheDirectory());
    }

    public function testUsesDefaultSuitesWhenEmpty(): void
    {
        $default = new DefaultConfiguration();
        $config = new LoadedConfiguration($default);

        self::assertCount(1, $default->getSuites());
        self::assertCount(1, $config->getSuites());
        self::assertSame($default->getSuites()['main'], $config->getSuites()['main']);
    }

    public function testOverridesSuites(): void
    {
        $default = new DefaultConfiguration();
        $config = new LoadedConfiguration($default);

        $suite = new SuiteValue('other', '/myscenario');
        $config->setSuites(['other' => $suite]);

        self::assertSame(['other' => $suite], $config->getSuites());
    }

    public function testUsesDefaultConnectionsWhenEmpty(): void
    {
        $default = new DefaultConfiguration();
        $config = new LoadedConfiguration($default);

        self::assertSame($default->getConnections(), $config->getConnections());
    }

    public function testOverridesConnections(): void
    {
        $default = new DefaultConfiguration();
        $config = new LoadedConfiguration($default);

        $connection = new ConnectionValue('db', 'myconfig');
        $config->setConnections(['db' => $connection]);

        self::assertSame(['db' => $connection], $config->getConnections());
    }
}
