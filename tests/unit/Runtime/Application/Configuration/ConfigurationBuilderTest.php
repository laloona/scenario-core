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
use Scenario\Core\Application;
use Scenario\Core\Runtime\Application\Configuration\ConfigurationBuilder;
use Scenario\Core\Runtime\Application\Configuration\ConfigurationFinder;
use Scenario\Core\Runtime\Application\Configuration\DefaultConfiguration;
use Scenario\Core\Runtime\Application\Configuration\LoadedConfiguration;
use Scenario\Core\Runtime\Application\Configuration\Value\ConnectionValue;
use Scenario\Core\Runtime\Application\Configuration\Value\SuiteValue;
use Scenario\Core\Runtime\Application\Configuration\XMLParser;
use Scenario\Core\Runtime\Exception\BuilderException;
use function dirname;
use function file_put_contents;
use function mkdir;
use function rmdir;
use function scandir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

#[CoversClass(ConfigurationBuilder::class)]
#[UsesClass(ConfigurationFinder::class)]
#[UsesClass(XMLParser::class)]
#[UsesClass(DefaultConfiguration::class)]
#[UsesClass(LoadedConfiguration::class)]
#[UsesClass(ConnectionValue::class)]
#[UsesClass(SuiteValue::class)]
#[UsesClass(Application::class)]
#[UsesClass(BuilderException::class)]
#[Group('runtime')]
#[Small]
final class ConfigurationBuilderTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/scenario_config_' . uniqid();
        mkdir($this->tempDir);

        $property = new ReflectionClass(Application::class)->getProperty('rootDir');
        $property->setValue(null, $this->tempDir);
    }

    protected function tearDown(): void
    {
        foreach (scandir($this->tempDir) as $file) {
            if ($file !== '.' && $file !== '..') {
                unlink($this->tempDir . '/' . $file);
            }
        }

        rmdir($this->tempDir);

        $property = new ReflectionClass(Application::class)->getProperty('rootDir');
        $property->setValue(null, null);
    }

    public function testBuildReadsScenarioXmlAndPopulatesConfiguration(): void
    {
        $xml = <<<XML
<?xml version="1.0"?>
<scenario bootstrap="bootstrap.php" cacheDirectory=".cache">
  <database>
    <connection name="db">config/db.php</connection>
  </database>
  <suites>
    <suite name="main">
      <directory>scenarios</directory>
    </suite>
  </suites>
</scenario>
XML;
        file_put_contents($this->tempDir . '/scenario.xml', $xml);

        $config = new ConfigurationBuilder(
            new ConfigurationFinder(),
            new XMLParser($this->xsdPath()),
        )->build();

        self::assertSame('bootstrap.php', $config->getBootstrap());
        self::assertSame('.cache', $config->getCacheDirectory());

        $connections = $config->getConnections();
        self::assertArrayHasKey('db', $connections);
        self::assertSame('config/db.php', $connections['db']->config);

        $suites = $config->getSuites();
        self::assertArrayHasKey('main', $suites);
        self::assertSame('scenarios', $suites['main']->directory);
    }

    public function testBuildUsesDefaultsWhenAttributesAreMissing(): void
    {
        $xml = <<<XML
<?xml version="1.0"?>
<scenario>
  <database>
    <connection name="db">config/db.php</connection>
  </database>
  <suites>
    <suite name="main">
      <directory>scenarios</directory>
    </suite>
  </suites>
</scenario>
XML;
        file_put_contents($this->tempDir . '/scenario.xml', $xml);

        $config = new ConfigurationBuilder(
            new ConfigurationFinder(),
            new XMLParser($this->xsdPath()),
        )->build();
        $default = new DefaultConfiguration();

        self::assertSame($default->getBootstrap(), $config->getBootstrap());
        self::assertSame($default->getCacheDirectory(), $config->getCacheDirectory());
    }

    public function testBuildReturnsDefaultConfigurationWhenNoFileExists(): void
    {
        $config = new ConfigurationBuilder(
            new ConfigurationFinder(),
            new XMLParser($this->xsdPath()),
        )->build();

        self::assertInstanceOf(DefaultConfiguration::class, $config);
    }

    public function testBuildThrowsOnDuplicateSuiteNames(): void
    {
        $xml = <<<XML
<?xml version="1.0"?>
<scenario>
  <suites>
    <suite name="main">
      <directory>scenarios</directory>
    </suite>
    <suite name="main">
      <directory>other</directory>
    </suite>
  </suites>
</scenario>
XML;
        file_put_contents($this->tempDir . '/scenario.xml', $xml);

        $builder = new ConfigurationBuilder(
            new ConfigurationFinder(),
            new XMLParser($this->xsdPath()),
        );

        $this->expectException(BuilderException::class);
        $this->expectExceptionMessage('suite with name "main" already exists');

        $builder->build();
    }

    public function testBuildThrowsOnDuplicateConnectionNames(): void
    {
        $xml = <<<XML
<?xml version="1.0"?>
<scenario>
  <database>
    <connection name="db">config/db.php</connection>
    <connection name="db">config/db2.php</connection>
  </database>
  <suites>
    <suite name="main">
      <directory>scenarios</directory>
    </suite>
  </suites>
</scenario>
XML;
        file_put_contents($this->tempDir . '/scenario.xml', $xml);

        $this->expectException(BuilderException::class);
        $this->expectExceptionMessage('connection with name "db" already exists');

        new ConfigurationBuilder(
            new ConfigurationFinder(),
            new XMLParser($this->xsdPath()),
        )->build();
    }

    public function testBuildThrowsOnDuplicateConnectionNamesWhenNameIsMissing(): void
    {
        $xml = <<<XML
<?xml version="1.0"?>
<scenario>
  <database>
    <connection>config/db.php</connection>
    <connection>config/db2.php</connection>
  </database>
  <suites>
    <suite name="main">
      <directory>scenarios</directory>
    </suite>
  </suites>
</scenario>
XML;
        file_put_contents($this->tempDir . '/scenario.xml', $xml);

        $this->expectException(BuilderException::class);
        $this->expectExceptionMessage('connection with name "" already exists');

        new ConfigurationBuilder(
            new ConfigurationFinder(),
            new XMLParser($this->xsdPath()),
        )->build();
    }

    private function xsdPath(): string
    {
        return dirname(__DIR__, 5) . '/xsd/scenario.xsd';
    }
}
