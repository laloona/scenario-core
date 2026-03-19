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
use Scenario\Core\Attribute\ApplyScenario;
use Scenario\Core\Attribute\RefreshDatabase;
use Scenario\Core\Runtime\Application;
use Scenario\Core\Runtime\Application\Configuration\DefaultConfiguration;
use Scenario\Core\Runtime\Application\Configuration\Value\ConnectionValue;
use Scenario\Core\Runtime\Application\Configuration\Value\SuiteValue;
use Scenario\Core\Tests\Unit\ApplicationMock;

#[CoversClass(DefaultConfiguration::class)]
#[UsesClass(ApplyScenario::class)]
#[UsesClass(Application::class)]
#[UsesClass(ConnectionValue::class)]
#[UsesClass(RefreshDatabase::class)]
#[UsesClass(SuiteValue::class)]
#[Group('runtime')]
#[Small]
final class DefaultConfigurationTest extends TestCase
{
    use ApplicationMock;

    protected function setUp(): void
    {
        $this->resetApplication();
        $this->createRootDir();
    }

    protected function tearDown(): void
    {
        $this->resetApplication();
        $this->removeRootDir();
    }

    public function testReturnsExpectedDefaultValues(): void
    {
        $configuration = new DefaultConfiguration();
        $cacheKey = $configuration->getCacheKey();

        self::assertSame('', $configuration->getBootstrap());
        self::assertSame(Application::getRootDir() . '/.scenario.cache', $configuration->getCacheDirectory());
        self::assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $cacheKey);

        $suites = $configuration->getSuites();
        self::assertArrayHasKey('main', $suites);
        self::assertSame('main', $suites['main']->name);
        self::assertSame(Application::getRootDir() . '/scenario', $suites['main']->directory);

        self::assertSame([], $configuration->getConnections());
        self::assertSame([
            ApplyScenario::class,
            RefreshDatabase::class,
        ], $configuration->getAttributes());
    }

    public function testSetCacheKeyDoesNotChangeGeneratedCacheKey(): void
    {
        $configuration = new DefaultConfiguration();

        $configuration->setCacheKey('fixed-key');

        self::assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $configuration->getCacheKey());
        self::assertNotSame('fixed-key', $configuration->getCacheKey());
    }
}
