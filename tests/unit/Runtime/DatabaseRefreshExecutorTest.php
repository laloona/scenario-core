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
use Scenario\Core\Attribute\RefreshDatabase;
use Scenario\Core\Runtime\Application;
use Scenario\Core\Runtime\Application\Configuration\DefaultConfiguration;
use Scenario\Core\Runtime\Application\Configuration\LoadedConfiguration;
use Scenario\Core\Runtime\Application\Configuration\Value\ConnectionValue;
use Scenario\Core\Runtime\DatabaseRefreshExecutor;
use Scenario\Core\Runtime\Exception\Metadata\ConnectionException;
use Scenario\Core\Tests\Unit\ApplicationMock;
use function file_get_contents;
use function file_put_contents;
use const DIRECTORY_SEPARATOR;

#[CoversClass(DatabaseRefreshExecutor::class)]
#[UsesClass(RefreshDatabase::class)]
#[UsesClass(Application::class)]
#[UsesClass(DefaultConfiguration::class)]
#[UsesClass(LoadedConfiguration::class)]
#[UsesClass(ConnectionValue::class)]
#[UsesClass(ConnectionException::class)]
#[Group('runtime')]
#[Small]
final class DatabaseRefreshExecutorTest extends TestCase
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

    public function testExecuteIncludesConfiguredConnectionFile(): void
    {
        $configFile = 'refresh.php';
        file_put_contents(
            Application::getRootDir() . DIRECTORY_SEPARATOR . $configFile,
            <<<'PHP'
<?php declare(strict_types=1);

file_put_contents(__DIR__ . '/executed.flag', 'ok');
PHP,
        );

        $configuration = new LoadedConfiguration(new DefaultConfiguration());
        $configuration->setConnections([
            'main' => new ConnectionValue('main', $configFile),
        ]);

        $this->setConfiguration($configuration);

        $executor = new DatabaseRefreshExecutor();
        $executor->execute(new RefreshDatabase('main'));

        self::assertFileExists(Application::getRootDir() . DIRECTORY_SEPARATOR . 'executed.flag');
        self::assertSame('ok', file_get_contents(Application::getRootDir() . DIRECTORY_SEPARATOR . 'executed.flag'));
    }

    public function testExecuteThrowsExceptionWhenConnectionDoesNotExist(): void
    {
        $configuration = new LoadedConfiguration(new DefaultConfiguration());
        $configuration->setConnections([]);

        $this->setConfiguration($configuration);

        $executor = new DatabaseRefreshExecutor();

        $this->expectException(ConnectionException::class);
        $this->expectExceptionMessage('missing');

        $executor->execute(new RefreshDatabase('missing'));
    }

    public function testExecuteThrowsExceptionWhenConnectionFileDoesNotExist(): void
    {
        $configuration = new LoadedConfiguration(new DefaultConfiguration());
        $configuration->setConnections([
            'main' => new ConnectionValue('main', 'does-not-exist.php'),
        ]);

        $this->setConfiguration($configuration);

        $executor = new DatabaseRefreshExecutor();

        $this->expectException(ConnectionException::class);
        $this->expectExceptionMessage('main');

        $executor->execute(new RefreshDatabase('main'));
    }
}
