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
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Scenario\Core\Attribute\ApplyScenario;
use Scenario\Core\Attribute\RefreshDatabase;
use Scenario\Core\Runtime\Application;
use Scenario\Core\Runtime\Application\Configuration\DefaultConfiguration;
use Scenario\Core\Runtime\Application\Configuration\LoadedConfiguration;
use Scenario\Core\Runtime\Application\Configuration\Value\ConnectionValue;
use Scenario\Core\Runtime\Application\Configuration\Value\SuiteValue;
use function strlen;

#[CoversClass(LoadedConfiguration::class)]
#[UsesClass(ApplyScenario::class)]
#[UsesClass(Application::class)]
#[UsesClass(ConnectionValue::class)]
#[UsesClass(DefaultConfiguration::class)]
#[UsesClass(RefreshDatabase::class)]
#[UsesClass(SuiteValue::class)]
#[Group('runtime')]
#[Small]
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
        $config = new LoadedConfiguration(new DefaultConfiguration());
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
        $config = new LoadedConfiguration(new DefaultConfiguration());
        $config->setCacheDirectory('/mycache');

        self::assertSame('/mycache', $config->getCacheDirectory());
    }

    public function testUsesDefaultCacheKeyWhenNotSet(): void
    {
        $config = new LoadedConfiguration(new DefaultConfiguration());

        $cacheKey = $config->getCacheKey();

        self::assertNotEmpty($cacheKey);
        self::assertSame(32, strlen($cacheKey));
    }

    public function testOverridesCacheKey(): void
    {
        $config = new LoadedConfiguration(new DefaultConfiguration());

        $config->setCacheKey('my-cache-key');

        self::assertSame('my-cache-key', $config->getCacheKey());
    }

    public function testUsesDefaultSuitesWhenEmpty(): void
    {
        $default = new DefaultConfiguration();
        $config = new LoadedConfiguration($default);

        self::assertCount(1, $default->getSuites());
        self::assertCount(1, $config->getSuites());
        self::assertSame($default->getSuites()['main']->name, $config->getSuites()['main']->name);
        self::assertSame($default->getSuites()['main']->directory, $config->getSuites()['main']->directory);
    }

    public function testOverridesSuites(): void
    {
        $config = new LoadedConfiguration(new DefaultConfiguration());

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
        $config = new LoadedConfiguration(new DefaultConfiguration());

        $connection = new ConnectionValue('db', 'myconfig');
        $config->setConnections(['db' => $connection]);

        self::assertSame(['db' => $connection], $config->getConnections());
    }

    public function testUsesDefaultAttributes(): void
    {
        $config = new LoadedConfiguration(new DefaultConfiguration());

        self::assertSame([
            ApplyScenario::class,
            RefreshDatabase::class,
        ], $config->getAttributes());
    }
}
