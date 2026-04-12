<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Tests\Unit\Runtime\Application\Configuration;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Stateforge\Scenario\Core\Attribute\ApplyScenario;
use Stateforge\Scenario\Core\Attribute\RefreshDatabase;
use Stateforge\Scenario\Core\Runtime\Application;
use Stateforge\Scenario\Core\Runtime\Application\Configuration\DefaultConfiguration;
use Stateforge\Scenario\Core\Runtime\Application\Configuration\Value\ConnectionValue;
use Stateforge\Scenario\Core\Runtime\Application\Configuration\Value\SuiteValue;
use Stateforge\Scenario\Core\Tests\Unit\ApplicationMock;
use const DIRECTORY_SEPARATOR;

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
        self::assertSame('.scenario.cache', $configuration->getCacheDirectory());
        self::assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $cacheKey);
        self::assertSame('scenario' . DIRECTORY_SEPARATOR . 'parameter', $configuration->getParameterDirectory());
        self::assertSame(
            ['scenario' . DIRECTORY_SEPARATOR . 'parameter'],
            $configuration->getParameterDirectories(),
        );

        $suites = $configuration->getSuites();
        self::assertArrayHasKey('main', $suites);
        self::assertSame('main', $suites['main']->name);
        self::assertSame('scenario' . DIRECTORY_SEPARATOR . 'main', $suites['main']->directory);

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

    public function testAddParameterDirectoryAppendsAdditionalDirectoriesAfterDefault(): void
    {
        $configuration = new DefaultConfiguration();

        $configuration->addParameterDirectory('custom-parameter');
        $configuration->addParameterDirectory('other-parameter');

        self::assertSame(
            [
                'scenario' . DIRECTORY_SEPARATOR . 'parameter',
                'custom-parameter',
                'other-parameter',
            ],
            $configuration->getParameterDirectories(),
        );
    }
}
