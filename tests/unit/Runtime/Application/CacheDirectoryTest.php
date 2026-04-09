<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Tests\Unit\Runtime\Application;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Stateforge\Scenario\Core\Runtime\Application\CacheDirectory;
use function file_put_contents;
use function is_dir;
use function is_file;
use function mkdir;
use function rmdir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

#[CoversClass(CacheDirectory::class)]
#[Group('runtime')]
#[Small]
final class CacheDirectoryTest extends TestCase
{
    private string $tempDirectory;

    protected function setUp(): void
    {
        $this->tempDirectory = sys_get_temp_dir() . '/scenario-core-cache-' . uniqid('', true);
    }

    protected function tearDown(): void
    {
        if (is_file($this->tempDirectory . '/nested/keep.txt') === true) {
            unlink($this->tempDirectory . '/nested/keep.txt');
        }

        if (is_dir($this->tempDirectory . '/nested') === true) {
            rmdir($this->tempDirectory . '/nested');
        }

        if (is_file($this->tempDirectory . '/first.txt') === true) {
            unlink($this->tempDirectory . '/first.txt');
        }

        if (is_file($this->tempDirectory . '/second.txt') === true) {
            unlink($this->tempDirectory . '/second.txt');
        }

        if (is_dir($this->tempDirectory) === true) {
            rmdir($this->tempDirectory);
        }
    }

    public function testPrepareCreatesDirectoryWhenItDoesNotExist(): void
    {
        (new CacheDirectory())->prepare($this->tempDirectory);

        self::assertDirectoryExists($this->tempDirectory);
    }

    public function testPrepareRemovesFilesFromExistingDirectoryAndKeepsSubDirectories(): void
    {
        mkdir($this->tempDirectory, 0777, true);
        mkdir($this->tempDirectory . '/nested', 0777, true);
        file_put_contents($this->tempDirectory . '/first.txt', 'first');
        file_put_contents($this->tempDirectory . '/second.txt', 'second');
        file_put_contents($this->tempDirectory . '/nested/keep.txt', 'nested');

        (new CacheDirectory())->prepare($this->tempDirectory);

        self::assertFileDoesNotExist($this->tempDirectory . '/first.txt');
        self::assertFileDoesNotExist($this->tempDirectory . '/second.txt');
        self::assertDirectoryExists($this->tempDirectory . '/nested');
        self::assertFileExists($this->tempDirectory . '/nested/keep.txt');
    }
}
