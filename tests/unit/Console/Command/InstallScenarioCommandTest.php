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
use Scenario\Core\Console\Command\InstallScenarioCommand;
use Scenario\Core\Contract\CliInput;
use Scenario\Core\Contract\CliOutput;
use Scenario\Core\PHPUnit\Configuration\ConfigFinder;
use Scenario\Core\PHPUnit\Configuration\ConfigurationCheck;
use Scenario\Core\PHPUnit\Configuration\Configurator;
use Scenario\Core\PHPUnit\Extension;
use Scenario\Core\Runtime\Application;
use Scenario\Core\Runtime\Application\ApplicationState;
use Scenario\Core\Tests\Unit\ApplicationMock;
use function file_get_contents;
use function file_put_contents;

#[CoversClass(InstallScenarioCommand::class)]
#[UsesClass(Application::class)]
#[UsesClass(ApplicationState::class)]
#[UsesClass(CliCommand::class)]
#[UsesClass(Command::class)]
#[UsesClass(ConfigFinder::class)]
#[UsesClass(ConfigurationCheck::class)]
#[UsesClass(Configurator::class)]
#[UsesClass(Extension::class)]
#[Group('console')]
#[Small]
final class InstallScenarioCommandTest extends TestCase
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
            'Configure the extension for PHPUnit.',
            (new InstallScenarioCommand())->description(),
        );
    }

    public function testRunReturnsErrorWithQuietWhenExtensionIsAlreadyConfigured(): void
    {
        file_put_contents(
            Application::getRootDir() . '/phpunit.xml',
            '<?xml version="1.0"?><phpunit><extensions><bootstrap class="' . Extension::class . '"/></extensions></phpunit>',
        );

        $input = self::createStub(CliInput::class);
        $input->method('option')
            ->willReturnMap([
                ['quiet', true],
            ]);

        $output = $this->createMock(CliOutput::class);
        $output->expects(self::never())
            ->method('confirm');

        self::assertSame(
            Command::Error,
            (new InstallScenarioCommand())->run($input, $output),
        );
    }

    public function testRunReturnsErrorWhenExtensionIsAlreadyConfigured(): void
    {
        file_put_contents(
            Application::getRootDir() . '/phpunit.xml',
            '<?xml version="1.0"?><phpunit><extensions><bootstrap class="' . Extension::class . '"/></extensions></phpunit>',
        );

        $input = self::createStub(CliInput::class);
        $input->method('option')
            ->willReturnMap([
                ['quiet', false],
            ]);

        $output = $this->createMock(CliOutput::class);
        $output->expects(self::once())
            ->method('warn')
            ->with('Plaese don\'t use these commands on production systems as data will be modified.');
        $output->expects(self::once())
            ->method('error')
            ->with('The PHPUnit extension is already configured.');
        $output->expects(self::once())
            ->method('confirm')
            ->with('Do you want to continue?')
            ->willReturn(true);
        $output->expects(self::never())
            ->method('success');

        self::assertSame(
            Command::Error,
            (new InstallScenarioCommand())->run($input, $output),
        );
    }

    public function testRunConfiguresExtensionWithQuietWhenConfirmed(): void
    {
        file_put_contents(Application::getRootDir() . '/phpunit.xml', '<?xml version="1.0"?><phpunit></phpunit>');

        $input = self::createStub(CliInput::class);
        $input->method('option')
            ->willReturnMap([
                ['quiet', true],
            ]);

        $output = $this->createMock(CliOutput::class);
        $output->expects(self::never())
            ->method('warn');
        $output->expects(self::never())
            ->method('confirm');
        $output->expects(self::never())
            ->method('success');
        $output->expects(self::never())
            ->method('error');

        self::assertSame(
            Command::Success,
            (new InstallScenarioCommand())->run($input, $output),
        );

        $content = file_get_contents(Application::getRootDir() . '/phpunit.xml');
        self::assertIsString($content);
        self::assertStringContainsString('<bootstrap class="' . Extension::class . '"/>', $content);
    }

    public function testRunConfiguresExtensionWhenConfirmed(): void
    {
        file_put_contents(Application::getRootDir() . '/phpunit.xml', '<?xml version="1.0"?><phpunit></phpunit>');

        $input = self::createStub(CliInput::class);
        $input->method('option')
            ->willReturnMap([
                ['quiet', false],
            ]);

        $output = $this->createMock(CliOutput::class);
        $output->expects(self::once())
            ->method('warn')
            ->with('Plaese don\'t use these commands on production systems as data will be modified.');
        $matcher = self::exactly(2);
        $output->expects($matcher)
            ->method('confirm')
            ->willReturnCallback(function (string $question) use ($matcher) {
                switch ($matcher->numberOfInvocations()) {
                    case 1:
                        self::assertSame('Do you want to continue?', $question);
                        return true;
                    case 2:
                        self::assertSame('The installation adds the scenario extension to the PHPUnit config. Do you want to continue?', $question);
                        return true;
                }
                return false;
            });
        $output->expects(self::once())
            ->method('success')
            ->with('Scenario extension is configured for PHPUnit.');
        $output->expects(self::never())
            ->method('error');

        self::assertSame(
            Command::Success,
            (new InstallScenarioCommand())->run($input, $output),
        );

        $content = file_get_contents(Application::getRootDir() . '/phpunit.xml');
        self::assertIsString($content);
        self::assertStringContainsString('<bootstrap class="' . Extension::class . '"/>', $content);
    }

    public function testRunReturnsErrorWhenInstallationIsDeclined(): void
    {
        file_put_contents(Application::getRootDir() . '/phpunit.xml', '<?xml version="1.0"?><phpunit></phpunit>');

        $input = self::createStub(CliInput::class);
        $input->method('option')
            ->willReturnMap([
                ['quiet', false],
            ]);

        $output = $this->createMock(CliOutput::class);
        $output->expects(self::once())
            ->method('warn')
            ->with('Plaese don\'t use these commands on production systems as data will be modified.');
        $matcher = self::exactly(2);
        $output->expects($matcher)
            ->method('confirm')
            ->willReturnCallback(function (string $question) use ($matcher) {
                switch ($matcher->numberOfInvocations()) {
                    case 1:
                        self::assertSame('Do you want to continue?', $question);
                        return true;
                    case 2:
                        self::assertSame('The installation adds the scenario extension to the PHPUnit config. Do you want to continue?', $question);
                        return false;
                }
                return false;
            });
        $output->expects(self::once())
            ->method('error')
            ->with('Configuring PHPUnit failed.');
        $output->expects(self::never())
            ->method('success');

        self::assertSame(
            Command::Error,
            (new InstallScenarioCommand())->run($input, $output),
        );
    }
}
