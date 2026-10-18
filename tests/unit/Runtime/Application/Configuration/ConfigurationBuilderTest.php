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
use Stateforge\Scenario\Core\Extension;
use Stateforge\Scenario\Core\Runtime\Application;
use Stateforge\Scenario\Core\Runtime\Application\Configuration\ConfigurationBuilder;
use Stateforge\Scenario\Core\Runtime\Application\Configuration\ConfigurationFinder;
use Stateforge\Scenario\Core\Runtime\Application\Configuration\DefaultConfiguration;
use Stateforge\Scenario\Core\Runtime\Application\Configuration\LoadedConfiguration;
use Stateforge\Scenario\Core\Runtime\Application\Configuration\Value\ConnectionValue;
use Stateforge\Scenario\Core\Runtime\Application\Configuration\Value\SuiteValue;
use Stateforge\Scenario\Core\Runtime\Application\Configuration\XMLParser;
use Stateforge\Scenario\Core\Runtime\Exception\Application\ConnectionAlreadyExistsException;
use Stateforge\Scenario\Core\Runtime\Exception\Application\ExtensionWithoutCLassException;
use Stateforge\Scenario\Core\Runtime\Exception\Application\SuiteAlreadyExistsException;
use Stateforge\Scenario\Core\Runtime\Exception\Application\SuiteWithoutDirectoryException;
use Stateforge\Scenario\Core\Runtime\Exception\Application\XMLParserException;
use Stateforge\Scenario\Core\Runtime\Exception\InvalidExtensionSubClassException;
use Stateforge\Scenario\Core\Tests\Files\EmptyExtension;
use Stateforge\Scenario\Core\Tests\Files\OtherExtension;
use Stateforge\Scenario\Core\Tests\Unit\ApplicationMock;
use function dirname;
use function file_put_contents;

#[CoversClass(ConfigurationBuilder::class)]
#[UsesClass(Application::class)]
#[UsesClass(ConnectionAlreadyExistsException::class)]
#[UsesClass(ConfigurationFinder::class)]
#[UsesClass(ConnectionValue::class)]
#[UsesClass(DefaultConfiguration::class)]
#[UsesClass(LoadedConfiguration::class)]
#[UsesClass(ExtensionWithoutCLassException::class)]
#[UsesClass(InvalidExtensionSubClassException::class)]
#[UsesClass(SuiteAlreadyExistsException::class)]
#[UsesClass(SuiteWithoutDirectoryException::class)]
#[UsesClass(SuiteValue::class)]
#[UsesClass(XMLParser::class)]
#[UsesClass(XMLParserException::class)]
#[Group('runtime')]
#[Small]
final class ConfigurationBuilderTest extends TestCase
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

    public function testBuildReadsScenarioXmlAndPopulatesConfiguration(): void
    {
        $xml = <<<XML
<?xml version="1.0"?>
<scenario cacheDirectory=".cache" parameterDirectory="parameter-types">
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
        file_put_contents(Application::getRootDir() . '/scenario.xml', $xml);

        $config = (new ConfigurationBuilder(
            new ConfigurationFinder(),
            new XMLParser($this->xsdPath()),
        ))->build();

        self::assertInstanceOf(LoadedConfiguration::class, $config);
        self::assertSame([], $config->getExtensions());
        self::assertSame('.cache', $config->getCacheDirectory());
        self::assertSame('parameter-types', $config->getParameterDirectory());
        self::assertSame(['parameter-types'], $config->getParameterDirectories());

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
        file_put_contents(Application::getRootDir() . '/scenario.xml', $xml);

        $config = (new ConfigurationBuilder(
            new ConfigurationFinder(),
            new XMLParser($this->xsdPath()),
        ))->build();
        $default = new DefaultConfiguration();

        self::assertSame($default->getCacheDirectory(), $config->getCacheDirectory());
        self::assertSame($default->getParameterDirectory(), $config->getParameterDirectory());
        self::assertSame($default->getParameterDirectories(), $config->getParameterDirectories());
        self::assertSame($default->getExtensions(), $config->getExtensions());
    }

    public function testBuildReturnsDefaultConfigurationWhenNoFileExists(): void
    {
        $config = (new ConfigurationBuilder(
            new ConfigurationFinder(),
            new XMLParser($this->xsdPath()),
        ))->build();

        self::assertInstanceOf(DefaultConfiguration::class, $config);
    }

    public function testBuildRejectsRemovedBootstrapAttribute(): void
    {
        $xml = <<<XML
<?xml version="1.0"?>
<scenario bootstrap="bootstrap.php">
  <suites>
    <suite name="main">
      <directory>scenarios</directory>
    </suite>
  </suites>
</scenario>
XML;
        file_put_contents(Application::getRootDir() . '/scenario.xml', $xml);

        $this->expectException(XMLParserException::class);
        $this->expectExceptionMessage('configuration xml does not validate');

        (new ConfigurationBuilder(
            new ConfigurationFinder(),
            new XMLParser($this->xsdPath()),
        ))->build();
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
        file_put_contents(Application::getRootDir() . '/scenario.xml', $xml);

        $builder = new ConfigurationBuilder(
            new ConfigurationFinder(),
            new XMLParser($this->xsdPath()),
        );

        $this->expectException(SuiteAlreadyExistsException::class);
        $this->expectExceptionMessage('suite with name "main" already exists');

        $builder->build();
    }

    public function testBuildThrowsOnDuplicateSuiteNamesWhenEmptyNameCollidesWithMain(): void
    {
        $xml = <<<XML
<?xml version="1.0"?>
<scenario>
  <suites>
    <suite name="">
      <directory>scenarios</directory>
    </suite>
    <suite name="main">
      <directory>other</directory>
    </suite>
  </suites>
</scenario>
XML;
        file_put_contents(Application::getRootDir() . '/scenario.xml', $xml);

        $this->expectException(SuiteAlreadyExistsException::class);
        $this->expectExceptionMessage('suite with name "main" already exists');

        (new ConfigurationBuilder(
            new ConfigurationFinder(),
            new XMLParser($this->xsdPath()),
        ))->build();
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
        file_put_contents(Application::getRootDir() . '/scenario.xml', $xml);

        $this->expectException(ConnectionAlreadyExistsException::class);
        $this->expectExceptionMessage('connection with name "db" already exists');

        (new ConfigurationBuilder(
            new ConfigurationFinder(),
            new XMLParser($this->xsdPath()),
        ))->build();
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
        file_put_contents(Application::getRootDir() . '/scenario.xml', $xml);

        $this->expectException(ConnectionAlreadyExistsException::class);
        $this->expectExceptionMessage('connection with name "" already exists');

        (new ConfigurationBuilder(
            new ConfigurationFinder(),
            new XMLParser($this->xsdPath()),
        ))->build();
    }

    public function testBuildThrowsWhenSuiteDirectoryIsMissing(): void
    {
        $xml = <<<XML
<?xml version="1.0"?>
<scenario>
  <suites>
    <suite name="main">
      <directory></directory>
    </suite>
  </suites>
</scenario>
XML;
        file_put_contents(Application::getRootDir() . '/scenario.xml', $xml);

        $this->expectException(XMLParserException::class);
        $this->expectExceptionMessage('configuration xml does not validate');

        (new ConfigurationBuilder(
            new ConfigurationFinder(),
            new XMLParser($this->xsdPath()),
        ))->build();
    }

    public function testBuildUsesMainAsDefaultSuiteNameWhenNameAttributeIsMissing(): void
    {
        $xml = <<<XML
<?xml version="1.0"?>
<scenario>
  <suites>
    <suite name="">
      <directory>scenarios</directory>
    </suite>
  </suites>
</scenario>
XML;
        file_put_contents(Application::getRootDir() . '/scenario.xml', $xml);

        $config = (new ConfigurationBuilder(
            new ConfigurationFinder(),
            new XMLParser($this->xsdPath()),
        ))->build();

        $suites = $config->getSuites();
        self::assertArrayHasKey('main', $suites);
        self::assertSame('scenarios', $suites['main']->directory);
    }

    public function testBuildMapsMultipleConnectionsAndSuites(): void
    {
        $xml = <<<XML
<?xml version="1.0"?>
<scenario>
  <database>
    <connection name="db">config/db.php</connection>
    <connection name="reporting">config/reporting.php</connection>
  </database>
  <suites>
    <suite name="main">
      <directory>scenarios</directory>
    </suite>
    <suite name="secondary">
      <directory>other-scenarios</directory>
    </suite>
  </suites>
</scenario>
XML;
        file_put_contents(Application::getRootDir() . '/scenario.xml', $xml);

        $config = (new ConfigurationBuilder(
            new ConfigurationFinder(),
            new XMLParser($this->xsdPath()),
        ))->build();

        self::assertSame('config/db.php', $config->getConnections()['db']->config);
        self::assertSame('config/reporting.php', $config->getConnections()['reporting']->config);
        self::assertSame('scenarios', $config->getSuites()['main']->directory);
        self::assertSame('other-scenarios', $config->getSuites()['secondary']->directory);
    }

    public function testBuildInstantiatesConfiguredExtensionsInOrder(): void
    {
        $first = EmptyExtension::class;
        $second = OtherExtension::class;
        $this->writeExtensionConfiguration(
            '<bootstrap class="' . $first . '" />' .
            '<bootstrap class="' . $second . '" />',
        );

        $config = (new ConfigurationBuilder(
            new ConfigurationFinder(),
            new XMLParser($this->xsdPath()),
        ))->build();

        self::assertCount(2, $config->getExtensions());
        self::assertInstanceOf($first, $config->getExtensions()[0]);
        self::assertInstanceOf($second, $config->getExtensions()[1]);
    }

    public function testBuildRejectsExtensionWithoutClass(): void
    {
        $this->writeExtensionConfiguration('<bootstrap />');

        $this->expectException(ExtensionWithoutCLassException::class);
        $this->expectExceptionMessage('extension without class');

        (new ConfigurationBuilder(
            new ConfigurationFinder(),
            new XMLParser($this->xsdPath()),
        ))->build();
    }

    public function testBuildRejectsClassThatDoesNotImplementExtension(): void
    {
        $class = DefaultConfiguration::class;
        $this->writeExtensionConfiguration('<bootstrap class="' . $class . '" />');

        $this->expectException(InvalidExtensionSubClassException::class);
        $this->expectExceptionMessage($class . ' is not a subclass of ' . Extension::class);

        (new ConfigurationBuilder(
            new ConfigurationFinder(),
            new XMLParser($this->xsdPath()),
        ))->build();
    }

    private function writeExtensionConfiguration(string $bootstraps): void
    {
        file_put_contents(Application::getRootDir() . '/scenario.xml', <<<XML
<?xml version="1.0"?>
<scenario>
  <suites>
    <suite name="main">
      <directory>scenarios</directory>
    </suite>
  </suites>
  <extensions>{$bootstraps}</extensions>
</scenario>
XML);
    }

    private function xsdPath(): string
    {
        return dirname(__DIR__, 5) . '/xsd/scenario.xsd';
    }
}
