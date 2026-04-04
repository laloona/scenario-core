<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Tests\Unit\Runtime;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Stateforge\Scenario\Core\Attribute\ApplyScenario;
use Stateforge\Scenario\Core\Attribute\AsScenario;
use Stateforge\Scenario\Core\Attribute\Parameter;
use Stateforge\Scenario\Core\Attribute\RefreshDatabase;
use Stateforge\Scenario\Core\Runtime\Application;
use Stateforge\Scenario\Core\Runtime\Application\ApplicationState;
use Stateforge\Scenario\Core\Runtime\Application\Configuration\ConfigurationBuilder;
use Stateforge\Scenario\Core\Runtime\Application\Configuration\ConfigurationFinder;
use Stateforge\Scenario\Core\Runtime\Application\Configuration\DefaultConfiguration;
use Stateforge\Scenario\Core\Runtime\Application\Configuration\LoadedConfiguration;
use Stateforge\Scenario\Core\Runtime\Application\Configuration\Value\SuiteValue;
use Stateforge\Scenario\Core\Runtime\Application\Configuration\XMLParser;
use Stateforge\Scenario\Core\Runtime\ClassFinder;
use Stateforge\Scenario\Core\Runtime\Exception\RegistryException;
use Stateforge\Scenario\Core\Runtime\Metadata\Handler\ApplyScenarioHandler;
use Stateforge\Scenario\Core\Runtime\Metadata\Handler\RefreshDatabaseHandler;
use Stateforge\Scenario\Core\Runtime\Metadata\HandlerRegistry;
use Stateforge\Scenario\Core\Runtime\Metadata\ParameterType;
use Stateforge\Scenario\Core\Runtime\ScenarioDefinition;
use Stateforge\Scenario\Core\Runtime\ScenarioLoader;
use Stateforge\Scenario\Core\Runtime\ScenarioRegistry;
use Stateforge\Scenario\Core\Tests\Unit\ApplicationMock;
use Stateforge\Scenario\Core\Tests\Unit\ApplicationStateMock;
use Stateforge\Scenario\Core\Tests\Unit\HandlerRegistryMock;
use Stateforge\Scenario\Core\Tests\Unit\ScenarioRegistryMock;
use function file_get_contents;
use function file_put_contents;
use function mkdir;

#[CoversClass(Application::class)]
#[UsesClass(AsScenario::class)]
#[UsesClass(ApplicationState::class)]
#[UsesClass(ApplyScenario::class)]
#[UsesClass(ApplyScenarioHandler::class)]
#[UsesClass(ClassFinder::class)]
#[UsesClass(ConfigurationBuilder::class)]
#[UsesClass(ConfigurationFinder::class)]
#[UsesClass(DefaultConfiguration::class)]
#[UsesClass(HandlerRegistry::class)]
#[UsesClass(LoadedConfiguration::class)]
#[UsesClass(Parameter::class)]
#[UsesClass(ParameterType::class)]
#[UsesClass(RefreshDatabase::class)]
#[UsesClass(RefreshDatabaseHandler::class)]
#[UsesClass(RegistryException::class)]
#[UsesClass(ScenarioRegistry::class)]
#[UsesClass(ScenarioLoader::class)]
#[UsesClass(ScenarioDefinition::class)]
#[UsesClass(SuiteValue::class)]
#[UsesClass(XMLParser::class)]
#[Group('runtime')]
#[Small]
final class ApplicationTest extends TestCase
{
    use ApplicationMock;
    use ApplicationStateMock;
    use HandlerRegistryMock;
    use ScenarioRegistryMock;

    protected function setUp(): void
    {
        $this->resetApplication();
        $this->resetApplicationState();
        $this->resetHandlerRegistry();
        $this->resetScenarioRegistry();

        $this->createRootDir();
    }

    protected function tearDown(): void
    {
        $this->resetApplication();
        $this->resetApplicationState();
        $this->resetHandlerRegistry();
        $this->resetScenarioRegistry();
        $this->removeRootDir();
        ;
    }

    public function testBootstrapRegistersHandlersAndRunsBootstrapFile(): void
    {
        $bootstrapFile = Application::getRootDir() . '/bootstrap.php';
        file_put_contents($bootstrapFile, '<?php $GLOBALS["app_bootstrap"] = "ok";');

        $config = new LoadedConfiguration(new DefaultConfiguration());
        $config->setBootstrap('bootstrap.php');
        $this->setConfiguration($config);

        (new Application())->bootstrap();

        self::assertTrue(Application::isBooted());
        self::assertSame('ok', $GLOBALS['app_bootstrap'] ?? null);
        self::assertInstanceOf(
            RefreshDatabaseHandler::class,
            HandlerRegistry::getInstance()->attributeHandler(RefreshDatabase::class),
        );
        self::assertInstanceOf(
            ApplyScenarioHandler::class,
            HandlerRegistry::getInstance()->attributeHandler(ApplyScenario::class),
        );
    }

    public function testBootstrapFailsWhenBootstrapFileThrows(): void
    {
        $bootstrapFile = Application::getRootDir() . '/bootstrap.php';
        file_put_contents($bootstrapFile, '<?php throw new RuntimeException("something went wrong");');

        $config = new LoadedConfiguration(new DefaultConfiguration());
        $config->setBootstrap('bootstrap.php');
        $this->setConfiguration($config);

        (new Application())->bootstrap();

        self::assertFalse(Application::isBooted());
        self::assertTrue((new ApplicationState())->isFailed());

        $this->expectException(RegistryException::class);
        HandlerRegistry::getInstance()->attributeHandler(ApplyScenario::class);
    }

    public function testPrepareKeepsProvidedConfigurationButDontBoot(): void
    {
        $config = new LoadedConfiguration(new DefaultConfiguration());
        $this->setConfiguration($config);

        (new Application())->prepare();

        self::assertSame($config, Application::config());
        self::assertFalse(Application::isBooted());
    }

    public function testPrepareBuildsConfigurationWhenMissing(): void
    {
        $xsdSource = file_get_contents(__DIR__ . '/../../../xsd/scenario.xsd');
        self::assertIsString($xsdSource);

        mkdir(Application::getRootDir() . '/vendor/scenario/core/xsd', 0777, true);
        file_put_contents(Application::getRootDir() . '/vendor/scenario/core/xsd/scenario.xsd', $xsdSource);

        mkdir(Application::getRootDir() . '/scenarios');
        file_put_contents(Application::getRootDir() . '/scenarios/ScenarioY.php', <<<PHP
<?php declare(strict_types=1);
namespace Stateforge\\Scenario\\Core\\Tests\\Tmp;
use Stateforge\\Scenario\\Core\\Attribute\\AsScenario;
use Stateforge\\Scenario\\Core\\Contract\\ScenarioInterface;
use Stateforge\\Scenario\\Core\\Runtime\\ScenarioParameters;
#[AsScenario('prepared')]
final class ScenarioY implements ScenarioInterface
{
    public function configure(ScenarioParameters \$parameters): void {}
    public function up(): void {}
    public function down(): void {}
}
PHP);

        file_put_contents(Application::getRootDir() . '/scenario.xml', <<<XML
<?xml version="1.0"?>
<scenario>
  <suites>
    <suite name="main">
      <directory>scenarios</directory>
    </suite>
  </suites>
</scenario>
XML);

        (new Application())->prepare();

        self::assertNotNull(Application::config());
        self::assertInstanceOf(ScenarioDefinition::class, ScenarioRegistry::getInstance()->resolve('Stateforge\\Scenario\\Core\\Tests\\Tmp\\ScenarioY'));
    }

    public function testGetRootDirComputesWhenNotCached(): void
    {
        $this->resetApplication();

        self::assertNotEmpty(Application::getRootDir());
    }

    public function testConfigReturnsNullWhenNotSet(): void
    {
        $this->resetApplication();

        self::assertNull(Application::config());
    }
}
