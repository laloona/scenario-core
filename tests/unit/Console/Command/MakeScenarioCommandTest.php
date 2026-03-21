<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Tests\Unit\Console\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Scenario\Core\Console\Command\CliCommand;
use Scenario\Core\Console\Command\Command;
use Scenario\Core\Console\Command\MakeScenarioCommand;
use Scenario\Core\Contract\CliInput;
use Scenario\Core\Contract\CliOutput;
use Scenario\Core\Runtime\Application;
use Scenario\Core\Runtime\Application\ApplicationState;
use Scenario\Core\Runtime\Application\Configuration\DefaultConfiguration;
use Scenario\Core\Runtime\Application\Configuration\LoadedConfiguration;
use Scenario\Core\Runtime\Application\Configuration\Value\SuiteValue;
use Scenario\Core\Tests\Unit\ApplicationMock;
use function file_get_contents;
use function file_put_contents;
use function is_file;
use function mkdir;

#[CoversClass(MakeScenarioCommand::class)]
#[UsesClass(Application::class)]
#[UsesClass(ApplicationState::class)]
#[UsesClass(CliCommand::class)]
#[UsesClass(Command::class)]
#[UsesClass(DefaultConfiguration::class)]
#[UsesClass(LoadedConfiguration::class)]
#[UsesClass(SuiteValue::class)]
#[Group('console')]
#[Small]
final class MakeScenarioCommandTest extends TestCase
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

    public function testDescriptionReturnsExpectedText(): void
    {
        self::assertSame(
            'Make a scenario or config file.',
            (new MakeScenarioCommand())->description(),
        );
    }

    public function testRunGeneratesScenarioFile(): void
    {
        mkdir(Application::getRootDir() . '/vendor/scenario/core/blueprint', 0777, true);
        mkdir(Application::getRootDir() . '/app/scenarios', 0777, true);

        file_put_contents(
            Application::getRootDir() . '/vendor/scenario/core/blueprint/scenario.blueprint',
            <<<'PHP'
<?php declare(strict_types=1);

namespace %nameSpace%;

final class %className%
{
}
PHP,
        );

        $config = new LoadedConfiguration(new DefaultConfiguration());
        $config->setSuites([
            'main' => new SuiteValue('main', 'app/scenarios'),
        ]);
        $this->setConfiguration($config);

        $input = self::createStub(CliInput::class);
        $input->method('option')
            ->willReturnMap([
                ['quiet', true],
            ]);
        $input->method('argument')
            ->willReturnMap([
                ['0', 'scenario'],
            ]);

        $output = $this->createMock(CliOutput::class);
        $output->expects(self::once())
            ->method('ask')
            ->with('Please insert a class name for the new scenario', null, self::isCallable())
            ->willReturn('MyScenario');
        $output->expects(self::once())
            ->method('success')
            ->with(self::stringContains('/app/scenarios/MyScenario.php'));
        $output->expects(self::never())
            ->method('error');

        $result = (new MakeScenarioCommand())->run($input, $output);

        $scenarioFile = Application::getRootDir() . '/app/scenarios/MyScenario.php';
        self::assertSame(Command::Success, $result);
        self::assertTrue(is_file($scenarioFile));
        self::assertStringContainsString('namespace App\\Scenarios;', (string) file_get_contents($scenarioFile));
        self::assertStringContainsString('final class MyScenario', (string) file_get_contents($scenarioFile));
    }

    public function testRunGeneratesConfigFileWhenChosenInteractively(): void
    {
        mkdir(Application::getRootDir() . '/vendor/scenario/core/blueprint', 0777, true);
        file_put_contents(
            Application::getRootDir() . '/vendor/scenario/core/blueprint/config.blueprint',
            "<scenario></scenario>\n",
        );

        $input = self::createStub(CliInput::class);
        $input->method('option')
            ->willReturnMap([
                ['quiet', true],
            ]);
        $input->method('argument')
            ->willReturnMap([
                ['0', null],
            ]);

        $output = $this->createMock(CliOutput::class);
        $output->expects(self::once())
            ->method('choice')
            ->with('Please select the type do would like to make.', ['scenario', 'config'], '0')
            ->willReturn('1');
        $output->expects(self::once())
            ->method('success')
            ->with('Config file generated, please modify to your needs.');
        $output->expects(self::never())
            ->method('error');

        $result = (new MakeScenarioCommand())->run($input, $output);

        $configFile = Application::getRootDir() . '/scenario.dist.xml';
        self::assertSame(Command::Success, $result);
        self::assertTrue(is_file($configFile));
        self::assertSame("<scenario></scenario>\n", file_get_contents($configFile));
    }
}
