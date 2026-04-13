<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Tests\Unit\Runtime\Metadata\Parameter;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Stateforge\Scenario\Core\Attribute\AsParameterType;
use Stateforge\Scenario\Core\ParameterTypeDefinition;
use Stateforge\Scenario\Core\Runtime\Application;
use Stateforge\Scenario\Core\Runtime\Application\CacheDirectory;
use Stateforge\Scenario\Core\Runtime\Application\Configuration\Configuration;
use Stateforge\Scenario\Core\Runtime\ClassFinder;
use Stateforge\Scenario\Core\Runtime\Exception\Metadata\InvalidParameterTypeException;
use Stateforge\Scenario\Core\Runtime\Exception\Metadata\UnknownParameterTypeException;
use Stateforge\Scenario\Core\Runtime\Metadata\Parameter\ParameterTypeLoader;
use Stateforge\Scenario\Core\Runtime\Metadata\Parameter\ParameterTypeRegistry;
use Stateforge\Scenario\Core\Runtime\Metadata\ValueType\IntegerType;
use Stateforge\Scenario\Core\Tests\Unit\ApplicationMock;
use stdClass;
use function array_map;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function filemtime;
use function json_encode;
use function md5;
use function mkdir;
use function touch;
use function uniqid;
use const DIRECTORY_SEPARATOR;

#[CoversClass(ParameterTypeLoader::class)]
#[UsesClass(Application::class)]
#[UsesClass(AsParameterType::class)]
#[UsesClass(CacheDirectory::class)]
#[UsesClass(ClassFinder::class)]
#[UsesClass(IntegerType::class)]
#[UsesClass(InvalidParameterTypeException::class)]
#[UsesClass(ParameterTypeRegistry::class)]
#[UsesClass(ParameterTypeDefinition::class)]
#[UsesClass(UnknownParameterTypeException::class)]
#[Group('runtime')]
#[Small]
final class ParameterTypeLoaderTest extends TestCase
{
    use ApplicationMock;

    protected function setUp(): void
    {
        $this->createRootDir();
    }

    protected function tearDown(): void
    {
        $this->removeRootDir();
        $this->resetApplication();

        $property = new ReflectionProperty(ParameterTypeRegistry::class, 'instance');
        $property->setValue(null, null);
    }

    private function parameterTypeFixture(string $className, string $description): string
    {
        return <<<PHP
<?php declare(strict_types=1);
namespace Stateforge\Scenario\Core\Tests\Tmp;

use Stateforge\Scenario\Core\Attribute\AsParameterType;
use Stateforge\Scenario\Core\ParameterTypeDefinition;
use Stateforge\Scenario\Core\Runtime\Metadata\ValueType\IntegerType;

#[AsParameterType('{$description}')]
final class {$className} extends ParameterTypeDefinition
{
    public function cast(mixed \$value): int|null
    {
        return (new IntegerType(\$value))->value;
    }

    protected function valueType(mixed \$value): IntegerType
    {
        return new IntegerType(\$value);
    }
}
PHP;
    }

    public function testLoadTypesRegistersTypesAndCreatesCacheOnFirstRun(): void
    {
        $parameterDir = Application::getRootDir() . DIRECTORY_SEPARATOR . 'parameters';
        $cacheDir = Application::getRootDir() . DIRECTORY_SEPARATOR . 'cache';
        mkdir($parameterDir, 0777, true);

        $className = 'CachedIntegerParameterType' . uniqid();
        $class = 'Stateforge\\Scenario\\Core\\Tests\\Tmp\\' . $className;
        $file = $parameterDir . DIRECTORY_SEPARATOR . $className . '.php';

        file_put_contents($file, $this->parameterTypeFixture($className, 'cached-parameter'));
        touch($file, 1_700_000_000);

        $configuration = $this->getConfigurationStub('parameters', $cacheDir, 'static-cache-key');
        $registry = ParameterTypeRegistry::getInstance();
        $loader = new ParameterTypeLoader($registry);

        $loader->loadTypes($configuration);

        $cacheFile = $cacheDir . DIRECTORY_SEPARATOR . 'parameter' . DIRECTORY_SEPARATOR . 'static-cache-key' . md5('1700000000');
        self::assertFileExists($cacheFile);
        self::assertSame(
            json_encode([$class => 'cached-parameter']),
            file_get_contents($cacheFile),
        );
        self::assertSame($class, $registry->resolve($class)::class);
        self::assertSame('cached-parameter', $registry->all()[$class]->description);
    }

    public function testLoadTypesRebuildsCacheWhenCacheContentIsCorrupted(): void
    {
        $parameterDir = Application::getRootDir() . DIRECTORY_SEPARATOR . 'parameters';
        $cacheDir = Application::getRootDir() . DIRECTORY_SEPARATOR . 'cache';
        mkdir($parameterDir, 0777, true);

        $className = 'RebuiltIntegerParameterType' . uniqid();
        $class = 'Stateforge\\Scenario\\Core\\Tests\\Tmp\\' . $className;
        $file = $parameterDir . DIRECTORY_SEPARATOR . $className . '.php';

        file_put_contents($file, $this->parameterTypeFixture($className, 'rebuilt-parameter'));
        touch($file, 1_700_000_002);

        $configuration = $this->getConfigurationStub('parameters', $cacheDir, 'static-cache-key');
        $cacheFile = $cacheDir . DIRECTORY_SEPARATOR . 'parameter' . DIRECTORY_SEPARATOR . 'static-cache-key' . md5('1700000002');

        (new ParameterTypeLoader(ParameterTypeRegistry::getInstance()))->loadTypes($configuration);
        file_put_contents($cacheFile, 'not-json');

        $property = new ReflectionProperty(ParameterTypeRegistry::class, 'instance');
        $property->setValue(null, null);

        $registry = ParameterTypeRegistry::getInstance();
        (new ParameterTypeLoader($registry))->loadTypes($configuration);

        self::assertNotSame('not-json', file_get_contents($cacheFile));
        self::assertSame($class, $registry->resolve($class)::class);
        self::assertSame('rebuilt-parameter', $registry->all()[$class]->description);
    }

    public function testLoadTypesUsesCacheWhenAvailable(): void
    {
        $parameterDir = Application::getRootDir() . DIRECTORY_SEPARATOR . 'parameters';
        $cacheDir = Application::getRootDir() . DIRECTORY_SEPARATOR . 'cache';
        mkdir($parameterDir, 0777, true);

        $className = 'ResolvedIntegerParameterType' . uniqid();
        $class = 'Stateforge\\Scenario\\Core\\Tests\\Tmp\\' . $className;
        $file = $parameterDir . DIRECTORY_SEPARATOR . $className . '.php';

        file_put_contents($file, $this->parameterTypeFixture($className, 'resolved-parameter'));
        touch($file, 1_700_000_001);

        $configuration = $this->getConfigurationStub('parameters', $cacheDir, 'static-cache-key');

        $loader = new ParameterTypeLoader(ParameterTypeRegistry::getInstance());
        $loader->loadTypes($configuration);
        $allFromClass = array_map(
            static fn (AsParameterType $asParameterType): ?string => $asParameterType->description,
            ParameterTypeRegistry::getInstance()->all(),
        );

        $property = new ReflectionProperty(ParameterTypeRegistry::class, 'instance');
        $property->setValue(null, null);

        $registry = ParameterTypeRegistry::getInstance();
        (new ParameterTypeLoader($registry))->loadTypes($configuration);

        $resolved = $registry->resolve($class);

        self::assertInstanceOf(ParameterTypeDefinition::class, $resolved);
        self::assertSame($class, $resolved::class);
        self::assertSame($class, $resolved->value);
        self::assertSame(
            $allFromClass,
            array_map(
                static fn (AsParameterType $asParameterType): ?string => $asParameterType->description,
                $registry->all(),
            ),
        );
        self::assertSame('resolved-parameter', $registry->all()[$class]->description);
    }

    public function testLoadTypesRebuildsWhenLoadingCacheThrows(): void
    {
        $parameterDir = Application::getRootDir() . DIRECTORY_SEPARATOR . 'parameters';
        $cacheDir = Application::getRootDir() . DIRECTORY_SEPARATOR . 'cache';
        mkdir($parameterDir, 0777, true);

        $className = 'ThrowableIntegerParameterType' . uniqid();
        $class = 'Stateforge\\Scenario\\Core\\Tests\\Tmp\\' . $className;
        $file = $parameterDir . DIRECTORY_SEPARATOR . $className . '.php';

        file_put_contents($file, $this->parameterTypeFixture($className, 'throwable-parameter'));
        touch($file, 1_700_000_005);

        $configuration = $this->getConfigurationStub('parameters', $cacheDir, 'static-cache-key');
        $cacheFile = $cacheDir . DIRECTORY_SEPARATOR . 'parameter' . DIRECTORY_SEPARATOR . 'static-cache-key' . md5('1700000005');

        (new ParameterTypeLoader(ParameterTypeRegistry::getInstance()))->loadTypes($configuration);
        file_put_contents($cacheFile, json_encode([
            stdClass::class => 'invalid',
        ]));

        $property = new ReflectionProperty(ParameterTypeRegistry::class, 'instance');
        $property->setValue(null, null);

        $registry = ParameterTypeRegistry::getInstance();
        (new ParameterTypeLoader($registry))->loadTypes($configuration);

        self::assertTrue(file_exists($cacheFile));
        self::assertSame($class, $registry->resolve($class)::class);
        self::assertSame('throwable-parameter', $registry->all()[$class]->description);
    }

    public function testLoadTypesSkipsInvalidCachedEntries(): void
    {
        $parameterDir = Application::getRootDir() . DIRECTORY_SEPARATOR . 'parameters';
        $cacheDir = Application::getRootDir() . DIRECTORY_SEPARATOR . 'cache';
        mkdir($parameterDir, 0777, true);

        $className = 'CachedOnlyIntegerParameterType' . uniqid();
        $class = 'Stateforge\\Scenario\\Core\\Tests\\Tmp\\' . $className;
        $file = $parameterDir . DIRECTORY_SEPARATOR . $className . '.php';

        file_put_contents($file, $this->parameterTypeFixture($className, 'cached-only-parameter'));
        touch($file, 1_700_000_003);

        $configuration = $this->getConfigurationStub('parameters', $cacheDir, 'static-cache-key');
        $cacheFile = $cacheDir . DIRECTORY_SEPARATOR . 'parameter' . DIRECTORY_SEPARATOR . 'static-cache-key' . md5('1700000003');

        (new ParameterTypeLoader(ParameterTypeRegistry::getInstance()))->loadTypes($configuration);
        file_put_contents($cacheFile, json_encode([
            'invalid' => ['broken'],
            $class => 'cached-only-parameter',
            123 => 'ignored',
        ]));

        $property = new ReflectionProperty(ParameterTypeRegistry::class, 'instance');
        $property->setValue(null, null);

        $registry = ParameterTypeRegistry::getInstance();
        (new ParameterTypeLoader($registry))->loadTypes($configuration);

        self::assertSame($class, $registry->resolve($class)::class);
        self::assertSame('cached-only-parameter', $registry->all()[$class]->description);
    }

    public function testLoadTypesDoesNothingWhenParameterDirectoryDoesNotExist(): void
    {
        $cacheDir = Application::getRootDir() . DIRECTORY_SEPARATOR . 'cache';
        $configuration = $this->getConfigurationStub('missing-parameters', $cacheDir, 'static-cache-key');

        $registry = ParameterTypeRegistry::getInstance();
        (new ParameterTypeLoader($registry))->loadTypes($configuration);

        $cacheFile = $cacheDir . DIRECTORY_SEPARATOR . 'parameter' . DIRECTORY_SEPARATOR . 'static-cache-key';
        self::assertFalse(file_exists($cacheFile));

        $this->expectException(UnknownParameterTypeException::class);
        $registry->resolve('Stateforge\\Scenario\\Core\\Tests\\Tmp\\MissingParameterType');
    }

    public function testLoadTypesSkipsAbstractParameterTypes(): void
    {
        $parameterDir = Application::getRootDir() . DIRECTORY_SEPARATOR . 'parameters';
        $cacheDir = Application::getRootDir() . DIRECTORY_SEPARATOR . 'cache';
        mkdir($parameterDir, 0777, true);

        $abstractClassName = 'AbstractIntegerParameterType' . uniqid();
        $abstractClass = 'Stateforge\\Scenario\\Core\\Tests\\Tmp\\' . $abstractClassName;
        $abstractFile = $parameterDir . DIRECTORY_SEPARATOR . 'AbstractType.php';

        file_put_contents($abstractFile, <<<PHP
<?php declare(strict_types=1);
namespace Stateforge\Scenario\Core\Tests\Tmp;

use Stateforge\Scenario\Core\Attribute\AsParameterType;
use Stateforge\Scenario\Core\ParameterTypeDefinition;
use Stateforge\Scenario\Core\Runtime\Metadata\ValueType\IntegerType;

#[AsParameterType('abstract-parameter')]
abstract class {$abstractClassName} extends ParameterTypeDefinition
{
    public function cast(mixed \$value): int|null
    {
        return (new IntegerType(\$value))->value;
    }

    protected function valueType(mixed \$value): IntegerType
    {
        return new IntegerType(\$value);
    }
}
PHP);
        $configuration = $this->getConfigurationStub('parameters', Application::getRootDir() . DIRECTORY_SEPARATOR . 'cache', 'static-cache-key');
        $cacheFile = $cacheDir . DIRECTORY_SEPARATOR . 'parameter' . DIRECTORY_SEPARATOR . 'static-cache-key' . md5((string) filemtime($abstractFile));
        $registry = ParameterTypeRegistry::getInstance();

        (new ParameterTypeLoader($registry))->loadTypes($configuration);

        self::assertFalse(file_exists($cacheFile));

        $this->expectException(UnknownParameterTypeException::class);
        $registry->resolve($abstractClass);
    }

    public function testLoadTypesSkipsClassesWithoutAsParameterTypeAttribute(): void
    {
        $parameterDir = Application::getRootDir() . DIRECTORY_SEPARATOR . 'parameters';
        $cacheDir = Application::getRootDir() . DIRECTORY_SEPARATOR . 'cache';
        mkdir($parameterDir, 0777, true);

        $className = 'UnattributedIntegerParameterType' . uniqid();
        $class = 'Stateforge\\Scenario\\Core\\Tests\\Tmp\\' . $className;
        $file = $parameterDir . DIRECTORY_SEPARATOR . $className . '.php';

        file_put_contents($file, <<<PHP
<?php declare(strict_types=1);
namespace Stateforge\Scenario\Core\Tests\Tmp;

use Stateforge\Scenario\Core\ParameterTypeDefinition;
use Stateforge\Scenario\Core\Runtime\Metadata\ValueType\IntegerType;

final class {$className} extends ParameterTypeDefinition
{
    public function cast(mixed \$value): int|null
    {
        return (new IntegerType(\$value))->value;
    }

    protected function valueType(mixed \$value): IntegerType
    {
        return new IntegerType(\$value);
    }
}
PHP);
        touch($file, 1_700_000_004);

        $configuration = $this->getConfigurationStub('parameters', $cacheDir, 'static-cache-key');
        $cacheFile = $cacheDir . DIRECTORY_SEPARATOR . 'parameter' . DIRECTORY_SEPARATOR . 'static-cache-key' . md5('1700000004');
        $registry = ParameterTypeRegistry::getInstance();

        (new ParameterTypeLoader($registry))->loadTypes($configuration);

        self::assertFalse(file_exists($cacheFile));
        self::assertSame([], $registry->all());

        $this->expectException(UnknownParameterTypeException::class);
        $registry->resolve($class);
    }

    private function getConfigurationStub(string $parameterDirectory, string $cacheDirectory, string $cacheKey): Configuration
    {
        $configuration = self::createStub(Configuration::class);
        $configuration->method('getParameterDirectory')->willReturn($parameterDirectory);
        $configuration->method('getParameterDirectories')->willReturn([$parameterDirectory]);
        $configuration->method('getCacheDirectory')->willReturn($cacheDirectory);
        $configuration->method('getCacheKey')->willReturn($cacheKey);

        return $configuration;
    }
}
