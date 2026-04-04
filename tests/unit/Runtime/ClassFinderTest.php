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
use ReflectionMethod;
use Stateforge\Scenario\Core\Runtime\Application;
use Stateforge\Scenario\Core\Runtime\ClassFinder;
use Stateforge\Scenario\Core\Tests\Unit\ApplicationMock;
use function file_put_contents;
use function md5;
use function mkdir;
use function sort;
use function touch;
use function uniqid;

#[CoversClass(ClassFinder::class)]
#[UsesClass(Application::class)]
#[Group('runtime')]
#[Small]
final class ClassFinderTest extends TestCase
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
    }

    public function testFindClassesInDirectoryReturnsOnlyClassesDefinedInDirectory(): void
    {
        $scanDir = Application::getRootDir() . '/scan';
        $nestedDir = $scanDir . '/Nested';
        $outsideDir = Application::getRootDir() . '/outside';
        mkdir($nestedDir, 0777, true);
        mkdir($outsideDir, 0777, true);

        $inDirectoryClass = 'FoundInDirectory' . uniqid();
        $nestedClass = 'FoundInNestedDirectory' . uniqid();
        $outsideClass = 'DefinedOutsideDirectory' . uniqid();

        file_put_contents($scanDir . '/InDirectory.php', <<<PHP
<?php declare(strict_types=1);
namespace Stateforge\Scenario\Core\Tests\Tmp;
final class {$inDirectoryClass}
{
}
PHP);

        file_put_contents($nestedDir . '/InNestedDirectory.php', <<<PHP
<?php declare(strict_types=1);
namespace Stateforge\Scenario\Core\Tests\Tmp;
final class {$nestedClass}
{
}
PHP);

        $outsideFile = $outsideDir . '/Outside.php';
        file_put_contents($outsideFile, <<<PHP
<?php declare(strict_types=1);
namespace Stateforge\Scenario\Core\Tests\Tmp;
final class {$outsideClass}
{
}
PHP);
        include_once $outsideFile;

        $classes = (new ClassFinder())->findClassesInDirectory($scanDir);
        sort($classes);

        self::assertSame(
            [
                'Stateforge\\Scenario\\Core\\Tests\\Tmp\\' . $inDirectoryClass,
                'Stateforge\\Scenario\\Core\\Tests\\Tmp\\' . $nestedClass,
            ],
            $classes,
        );
    }

    public function testFindClassesInDirectoryBuildsCacheKeyFromPhpFileModificationTimes(): void
    {
        $scanDir = Application::getRootDir() . '/cache';
        mkdir($scanDir, 0777, true);

        $file = $scanDir . '/CacheFixture.php';
        $className = 'CacheFixture' . uniqid();
        file_put_contents($file, <<<PHP
<?php declare(strict_types=1);
namespace Stateforge\Scenario\Core\Tests\Tmp;
final class {$className}
{
}
PHP);
        touch($file, 1_700_000_000);

        $finder = new ClassFinder();
        $finder->findClassesInDirectory($scanDir);

        self::assertSame(md5('1700000000'), $finder->getCacheKey());
    }

    public function testFindClassesInDirectoryReturnsEmptyArrayAndEmptyCacheKeyForEmptyDirectory(): void
    {
        $scanDir = Application::getRootDir() . '/empty';
        mkdir($scanDir, 0777, true);

        $finder = new ClassFinder();

        self::assertSame([], $finder->findClassesInDirectory($scanDir));
        self::assertSame('', $finder->getCacheKey());
    }

    public function testFindClassesInDirectoryIgnoresNonPhpFilesForCacheKeyAndDiscovery(): void
    {
        $scanDir = Application::getRootDir() . '/mixed';
        mkdir($scanDir, 0777, true);

        $phpFile = $scanDir . '/ScenarioFile.php';
        $textFile = $scanDir . '/Notes.txt';
        $className = 'MixedFixture' . uniqid();

        file_put_contents($phpFile, <<<PHP
<?php declare(strict_types=1);
namespace Stateforge\Scenario\Core\Tests\Tmp;
final class {$className}
{
}
PHP);
        file_put_contents($textFile, 'not a php file');

        touch($phpFile, 1_700_000_001);
        touch($textFile, 1_700_000_999);

        $finder = new ClassFinder();
        $classes = $finder->findClassesInDirectory($scanDir);

        self::assertSame(
            ['Stateforge\\Scenario\\Core\\Tests\\Tmp\\' . $className],
            $classes,
        );
        self::assertSame(md5('1700000001'), $finder->getCacheKey());
    }

    public function testFilterClassesByDirectoryReturnsEmptyArrayForInvalidDirectory(): void
    {
        $finder = new ClassFinder();
        $method = new ReflectionMethod($finder, 'filterClassesByDirectory');

        self::assertSame([], $method->invoke($finder, ['stdClass'], Application::getRootDir() . '/missing'));
    }

    public function testFilterClassesByDirectoryKeepsOnlyClassesFromGivenDirectory(): void
    {
        $scanDir = Application::getRootDir() . '/filter';
        $outsideDir = Application::getRootDir() . '/outside-filter';
        mkdir($scanDir, 0777, true);
        mkdir($outsideDir, 0777, true);

        $insideClass = 'FilterInside' . uniqid();
        $outsideClass = 'FilterOutside' . uniqid();

        $insideFile = $scanDir . '/Inside.php';
        file_put_contents($insideFile, <<<PHP
<?php declare(strict_types=1);
namespace Stateforge\Scenario\Core\Tests\Tmp;
final class {$insideClass}
{
}
PHP);
        include_once $insideFile;

        $outsideFile = $outsideDir . '/Outside.php';
        file_put_contents($outsideFile, <<<PHP
<?php declare(strict_types=1);
namespace Stateforge\Scenario\Core\Tests\Tmp;
final class {$outsideClass}
{
}
PHP);
        include_once $outsideFile;

        $finder = new ClassFinder();
        $method = new ReflectionMethod($finder, 'filterClassesByDirectory');

        self::assertSame(
            ['Stateforge\\Scenario\\Core\\Tests\\Tmp\\' . $insideClass],
            $method->invoke($finder, [
                'Stateforge\\Scenario\\Core\\Tests\\Tmp\\' . $insideClass,
                'Stateforge\\Scenario\\Core\\Tests\\Tmp\\' . $outsideClass,
            ], $scanDir),
        );
    }
}
