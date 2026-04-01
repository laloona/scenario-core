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
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Scenario\Core\Attribute\AsScenario;
use Scenario\Core\Attribute\Parameter;
use Scenario\Core\Runtime\Application;
use Scenario\Core\Runtime\Application\Configuration\DefaultConfiguration;
use Scenario\Core\Runtime\Application\Configuration\LoadedConfiguration;
use Scenario\Core\Runtime\Application\Configuration\Value\SuiteValue;
use Scenario\Core\Runtime\Exception\RegistryException;
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
use function json_encode;
use function mkdir;
use function uniqid;
use const DIRECTORY_SEPARATOR;

#[CoversClass(ScenarioLoader::class)]
#[UsesClass(Application::class)]
#[UsesClass(AsScenario::class)]
#[UsesClass(DefaultConfiguration::class)]
#[UsesClass(LoadedConfiguration::class)]
#[UsesClass(Parameter::class)]
#[UsesClass(ParameterType::class)]
#[UsesClass(RegistryException::class)]
#[UsesClass(ScenarioDefinition::class)]
#[UsesClass(ScenarioLoaderException::class)]
#[UsesClass(ScenarioRegistry::class)]
#[UsesClass(SuiteValue::class)]
#[Group('runtime')]
#[Medium]
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
        $definitionFromClass = ScenarioRegistry::getInstance()->resolve($scenario);

        $this->resetScenarioRegistry();

        (new ScenarioLoader(ScenarioRegistry::getInstance()))->loadScenarios($config);
        $definitionFromCache = ScenarioRegistry::getInstance()->resolve($scenario);

        self::assertSame('my-scenario', $definitionFromCache->name);
        self::assertSameDefinition($definitionFromClass, $definitionFromCache);
    }

    public function testLoadScenariosRebuildsWhenCacheIsCorrupted(): void
    {
        $scenario = $this->createScenarioSuite();
        $config = $this->getConfiguration();

        (new ScenarioLoader(ScenarioRegistry::getInstance()))->loadScenarios($config);

        $cacheFile = $config->getCacheDirectory() . DIRECTORY_SEPARATOR . $config->getCacheKey();
        file_put_contents($cacheFile, 'not-json');

        ScenarioRegistry::getInstance()->clear();

        (new ScenarioLoader(ScenarioRegistry::getInstance()))->loadScenarios($config);

        $definition = ScenarioRegistry::getInstance()->resolve($scenario);
        self::assertSame('my-scenario', $definition->name);
        self::assertSameDefinition($definition, ScenarioRegistry::getInstance()->resolve('my-scenario'));
        self::assertNotSame('not-json', file_get_contents($cacheFile));
    }

    public function testLoadScenariosSkipsInvalidCacheEntries(): void
    {
        $scenario = $this->createScenarioSuite();
        $config = $this->getConfiguration();

        (new ScenarioLoader(ScenarioRegistry::getInstance()))->loadScenarios($config);

        $cacheFile = $config->getCacheDirectory() . DIRECTORY_SEPARATOR . $config->getCacheKey();
        $payload = [
            'main' => [
                [
                    'class' => $scenario,
                    'name' => 'from-cache',
                    'description' => null,
                    'required' => false,
                    'repeatable' => true,
                    'parameters' => [],
                ],
                [
                    'class' => '',
                    'name' => 'invalid',
                    'description' => null,
                    'required' => true,
                    'repeatable' => false,
                    'parameters' => [],
                ],
                [
                    'class' => 'Unknown\\ClassName',
                    'name' => 'invalid2',
                    'description' => null,
                    'required' => false,
                    'repeatable' => false,
                    'parameters' => [],
                ],
            ],
        ];
        file_put_contents($cacheFile, json_encode($payload));

        ScenarioRegistry::getInstance()->clear();

        (new ScenarioLoader(ScenarioRegistry::getInstance()))->loadScenarios($config);

        self::assertSame($scenario, ScenarioRegistry::getInstance()->resolve($scenario)->class);
        self::assertSame($scenario, ScenarioRegistry::getInstance()->resolve('from-cache')->class);
    }

    public function testLoadScenariosRebuildsCacheAndRemovesOldFiles(): void
    {
        $scenario = $this->createScenarioSuite();
        $config = $this->getConfiguration();

        (new ScenarioLoader(ScenarioRegistry::getInstance()))->loadScenarios($config);

        $cacheDir = $config->getCacheDirectory();
        $cacheFile = $cacheDir . DIRECTORY_SEPARATOR . $config->getCacheKey();
        $oldFile = $cacheDir . DIRECTORY_SEPARATOR . 'old.cache';
        file_put_contents($oldFile, 'old');
        file_put_contents($cacheFile, 'not-json');

        ScenarioRegistry::getInstance()->clear();

        (new ScenarioLoader(ScenarioRegistry::getInstance()))->loadScenarios($config);

        self::assertFalse(is_file($oldFile));
        self::assertTrue(is_file($cacheFile));
        self::assertSame($scenario, ScenarioRegistry::getInstance()->resolve('my-scenario')->class);
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

    public function testLoadScenariosCreatesEmptyCacheWhenNoScenarioDefinitionsWereFound(): void
    {
        mkdir(Application::getRootDir() . '/scenarios');
        file_put_contents(Application::getRootDir() . '/scenarios/Helper.php', <<<'PHP'
<?php declare(strict_types=1);
namespace Scenario\Core\Tests\Tmp;
final class Helper
{
}
PHP);

        $config = $this->getConfiguration();

        (new ScenarioLoader(ScenarioRegistry::getInstance()))->loadScenarios($config);

        self::assertSame([], ScenarioRegistry::getInstance()->all());
        self::assertTrue(is_file($config->getCacheDirectory() . DIRECTORY_SEPARATOR . $config->getCacheKey()));
    }

    public function testLoadScenariosStoresEmptyCacheKeyWhenSuiteContainsNoPhpClasses(): void
    {
        mkdir(Application::getRootDir() . '/scenarios');

        $config = $this->getConfiguration();

        (new ScenarioLoader(ScenarioRegistry::getInstance()))->loadScenarios($config);

        self::assertSame('', $config->getCacheKey());
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
#[Parameter('id', ParameterType::Integer, required: true, repeatable: true, default: null)]
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

    private static function assertSameDefinition(ScenarioDefinition $expected, ScenarioDefinition $actual): void
    {
        self::assertSame($expected->name, $actual->name);
        self::assertSame($expected->class, $actual->class);
        self::assertSame($expected->suite, $actual->suite);
        foreach ($expected->parameters as $key => $parameter) {
            self::assertInstanceOf(Parameter::class, $parameter);
            self::assertInstanceOf(Parameter::class, $actual->parameters[$key]);
            self::assertSame($parameter->name, $actual->parameters[$key]->name);
            self::assertSame($parameter->type, $actual->parameters[$key]->type);
            self::assertSame($parameter->description, $actual->parameters[$key]->description);
            self::assertSame($parameter->required, $actual->parameters[$key]->required);
            self::assertSame($parameter->repeatable, $actual->parameters[$key]->repeatable);
            self::assertSame($parameter->default, $actual->parameters[$key]->default);
        }
    }
}
