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
use Scenario\Core\Runtime\Application;
use Scenario\Core\Runtime\ClassFinder;
use Scenario\Core\Tests\Unit\ApplicationMock;
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
namespace Scenario\Core\Tests\Tmp;
final class {$inDirectoryClass}
{
}
PHP);

        file_put_contents($nestedDir . '/InNestedDirectory.php', <<<PHP
<?php declare(strict_types=1);
namespace Scenario\Core\Tests\Tmp;
final class {$nestedClass}
{
}
PHP);

        $outsideFile = $outsideDir . '/Outside.php';
        file_put_contents($outsideFile, <<<PHP
<?php declare(strict_types=1);
namespace Scenario\Core\Tests\Tmp;
final class {$outsideClass}
{
}
PHP);
        include_once $outsideFile;

        $classes = (new ClassFinder())->findClassesInDirectory($scanDir);
        sort($classes);

        self::assertSame(
            [
                'Scenario\\Core\\Tests\\Tmp\\' . $inDirectoryClass,
                'Scenario\\Core\\Tests\\Tmp\\' . $nestedClass,
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
namespace Scenario\Core\Tests\Tmp;
final class {$className}
{
}
PHP);
        touch($file, 1_700_000_000);

        $finder = new ClassFinder();
        $finder->findClassesInDirectory($scanDir);

        self::assertSame(md5('1700000000'), $finder->getCacheKey());
    }
}
