<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Tests\Unit\Runtime;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Scenario\Core\Application;
use Scenario\Core\Attribute\AsScenario;
use Scenario\Core\Attribute\Parameter;
use Scenario\Core\Runtime\Application\Configuration\DefaultConfiguration;
use Scenario\Core\Runtime\Application\Configuration\LoadedConfiguration;
use Scenario\Core\Runtime\Application\Configuration\Value\SuiteValue;
use Scenario\Core\Runtime\Exception\ScenarioLoaderException;
use Scenario\Core\Runtime\Metadata\ParameterType;
use Scenario\Core\Runtime\ScenarioDefinition;
use Scenario\Core\Runtime\ScenarioLoader;
use Scenario\Core\Runtime\ScenarioRegistry;
use Scenario\Core\Tests\Unit\ApplicationMock;
use Scenario\Core\Tests\Unit\ScenarioRegistryMock;
use function file_get_contents;
use function file_put_contents;
use function is_file;
use function mkdir;
use function uniqid;

#[CoversClass(ScenarioLoader::class)]
#[UsesClass(Application::class)]
#[UsesClass(DefaultConfiguration::class)]
#[UsesClass(LoadedConfiguration::class)]
#[UsesClass(SuiteValue::class)]
#[UsesClass(ScenarioDefinition::class)]
#[UsesClass(ScenarioRegistry::class)]
#[UsesClass(AsScenario::class)]
#[UsesClass(Parameter::class)]
#[UsesClass(ParameterType::class)]
#[UsesClass(ScenarioLoaderException::class)]
#[Group('runtime')]
#[Small]
final class ScenarioLoaderTest extends TestCase
{
    use ApplicationMock;
    use ScenarioRegistryMock;

    protected function setUp(): void
    {
        $this->createRootDir();
    }

    protected function tearDown(): void
    {
        $this->resetScenarioRegistry();
        $this->resetApplication();
        $this->removeRootDir();
    }

    public function testLoadScenariosRegistersDefinitionsAndCreatesCache(): void
    {
        $scenario = $this->createScenarioSuite();
        $config = $this->getConfiguration();

        (new ScenarioLoader(ScenarioRegistry::getInstance()))->loadScenarios($config);

        $definition = ScenarioRegistry::getInstance()->resolve($scenario);
        self::assertSame('my-scenario', $definition->name);
        self::assertSame($definition, ScenarioRegistry::getInstance()->resolve('my-scenario'));
        self::assertCount(1, $definition->parameters);
        self::assertSame('id', $definition->parameters[0]->name);

        $cacheFile = $config->getCacheDirectory() . DIRECTORY_SEPARATOR . $config->getCacheKey();
        self::assertTrue(is_file($cacheFile));
    }

    public function testLoadScenariosUsesCacheWhenAvailable(): void
    {
        $scenario = $this->createScenarioSuite();
        $config = $this->getConfiguration();

        (new ScenarioLoader(ScenarioRegistry::getInstance()))->loadScenarios($config);
        $this->resetScenarioRegistry();

        (new ScenarioLoader(ScenarioRegistry::getInstance()))->loadScenarios($config);

        $definition = ScenarioRegistry::getInstance()->resolve($scenario);
        self::assertSame('my-scenario', $definition->name);
        self::assertSame($definition, ScenarioRegistry::getInstance()->resolve('my-scenario'));
    }

    public function xxtestLoadScenariosRebuildsWhenCacheIsCorrupted(): void
    {
        $scenario = $this->createScenarioSuite();
        $config = $this->getConfiguration();

        (new ScenarioLoader(ScenarioRegistry::getInstance()))->loadScenarios($config);

        $cacheFile = $config->getCacheDirectory() . DIRECTORY_SEPARATOR . $config->getCacheKey();
        file_put_contents($cacheFile, 'not-json');

        $this->resetScenarioRegistry();

        (new ScenarioLoader(ScenarioRegistry::getInstance()))->loadScenarios($config);

        $definition = ScenarioRegistry::getInstance()->resolve($scenario);
        self::assertSame('my-scenario', $definition->name);
        self::assertSame($definition, ScenarioRegistry::getInstance()->resolve('my-scenario'));
        self::assertNotSame('not-json', file_get_contents($cacheFile));
    }

    public function testLoadScenariosThrowsForMissingSuiteDirectory(): void
    {
        $config = $this->getConfiguration();
        $config->setSuites([
            'main' => new SuiteValue('main', 'missing-dir'),
        ]);

        $this->expectException(ScenarioLoaderException::class);

        (new ScenarioLoader(ScenarioRegistry::getInstance()))->loadScenarios($config);
    }

    private function createScenarioSuite(): string
    {
        $scenarioDir = Application::getRootDir() . '/scenarios';
        mkdir($scenarioDir);

        $namespace = 'Scenario\\Core\\Tests\\Tmp' . uniqid();
        $className = 'ScenarioA' . uniqid();

        $fileContent = <<<PHP
<?php declare(strict_types=1);
namespace {$namespace};
use Scenario\\Core\\Attribute\\AsScenario;
use Scenario\\Core\\Attribute\\Parameter;
use Scenario\\Core\\Contract\\ScenarioInterface;
use Scenario\\Core\\Runtime\\Metadata\\ParameterType;
use Scenario\\Core\\Runtime\\ScenarioParameters;

#[AsScenario('my-scenario')]
#[Parameter('id', ParameterType::Integer, required: true)]
final class {$className} implements ScenarioInterface
{
    public function configure(ScenarioParameters \$parameters): void {}
    public function up(): void {}
    public function down(): void {}
}
PHP;
        file_put_contents($scenarioDir . '/ScenarioA.php', $fileContent);

        return $namespace . '\\' . $className;
    }

    private function getConfiguration(): LoadedConfiguration
    {
        $config = new LoadedConfiguration(new DefaultConfiguration());
        $config->setCacheDirectory(Application::getRootDir() . '/.cache');
        $config->setSuites([
            'main' => new SuiteValue('main', 'scenarios'),
        ]);

        return $config;
    }
}
