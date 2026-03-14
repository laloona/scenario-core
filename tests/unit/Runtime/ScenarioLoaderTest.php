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
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
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
use SplFileInfo;
use function file_get_contents;
use function file_put_contents;
use function is_dir;
use function is_file;
use function mkdir;
use function rmdir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

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
    private string $rootDir;

    protected function setUp(): void
    {
        $this->rootDir = sys_get_temp_dir() . '/scenario_loader_' . uniqid();
        mkdir($this->rootDir);
        $this->setRootDir($this->rootDir);
    }

    protected function tearDown(): void
    {
        $this->resetScenarioRegistry();
        $this->setRootDir(null);
        $this->removeDir($this->rootDir);
    }

    public function testLoadScenariosRegistersDefinitionsAndCreatesCache(): void
    {
        $setup = $this->createScenarioSuite();
        $config = $setup['config'];

        (new ScenarioLoader(ScenarioRegistry::getInstance()))->loadScenarios($config);

        $definition = ScenarioRegistry::getInstance()->resolve($setup['fqcn']);
        self::assertSame('my-scenario', $definition->name);
        self::assertSame($definition, ScenarioRegistry::getInstance()->resolve('my-scenario'));
        self::assertCount(1, $definition->parameters);
        self::assertSame('id', $definition->parameters[0]->name);

        $cacheFile = $config->getCacheDirectory() . DIRECTORY_SEPARATOR . $config->getCacheKey();
        self::assertTrue(is_file($cacheFile));
    }

    public function testLoadScenariosUsesCacheWhenAvailable(): void
    {
        $setup = $this->createScenarioSuite();
        $config = $setup['config'];

        (new ScenarioLoader(ScenarioRegistry::getInstance()))->loadScenarios($config);
        $this->resetScenarioRegistry();

        (new ScenarioLoader(ScenarioRegistry::getInstance()))->loadScenarios($config);

        $definition = ScenarioRegistry::getInstance()->resolve($setup['fqcn']);
        self::assertSame('my-scenario', $definition->name);
        self::assertSame($definition, ScenarioRegistry::getInstance()->resolve('my-scenario'));
    }

    public function xxtestLoadScenariosRebuildsWhenCacheIsCorrupted(): void
    {
        $setup = $this->createScenarioSuite();
        $config = $setup['config'];

        (new ScenarioLoader(ScenarioRegistry::getInstance()))->loadScenarios($config);

        $cacheFile = $config->getCacheDirectory() . DIRECTORY_SEPARATOR . $config->getCacheKey();
        file_put_contents($cacheFile, 'not-json');

        $this->resetScenarioRegistry();

        (new ScenarioLoader(ScenarioRegistry::getInstance()))->loadScenarios($config);

        $definition = ScenarioRegistry::getInstance()->resolve($setup['fqcn']);
        self::assertSame('my-scenario', $definition->name);
        self::assertSame($definition, ScenarioRegistry::getInstance()->resolve('my-scenario'));
        self::assertNotSame('not-json', file_get_contents($cacheFile));
    }

    public function testLoadScenariosThrowsForMissingSuiteDirectory(): void
    {
        $config = new LoadedConfiguration(new DefaultConfiguration());
        $config->setCacheDirectory($this->rootDir . '/.cache');
        $config->setSuites([
            'main' => new SuiteValue('main', 'missing-dir'),
        ]);

        $this->expectException(ScenarioLoaderException::class);

        (new ScenarioLoader(ScenarioRegistry::getInstance()))->loadScenarios($config);
    }

    private function setRootDir(?string $dir): void
    {
        $reflection = new ReflectionClass(Application::class);
        $property = $reflection->getProperty('rootDir');
        $property->setValue(null, $dir);
    }

    /**
     * @return array{config: LoadedConfiguration, fqcn: string}
     */
    private function createScenarioSuite(): array
    {
        $scenarioDir = $this->rootDir . '/scenarios';
        mkdir($scenarioDir);

        $namespace = 'Scenario\\Core\\Tests\\Tmp' . uniqid();
        $className = 'ScenarioA' . uniqid();
        $fqcn = $namespace . '\\' . $className;

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

        $config = new LoadedConfiguration(new DefaultConfiguration());
        $config->setCacheDirectory($this->rootDir . '/.cache');
        $config->setSuites([
            'main' => new SuiteValue('main', 'scenarios'),
        ]);

        return [
            'config' => $config,
            'fqcn' => $fqcn,
        ];
    }

    private function resetScenarioRegistry(): void
    {
        $property = new ReflectionClass(ScenarioRegistry::class)->getProperty('instance');
        $property->setValue(null, null);
    }

    private function removeDir(string $dir): void
    {
        if (is_dir($dir) === false) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->isDir() === true) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }

        rmdir($dir);
    }
}
