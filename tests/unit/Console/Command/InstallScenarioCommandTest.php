<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Tests\Unit\Console\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Stateforge\Scenario\Core\Console\Command\CliCommand;
use Stateforge\Scenario\Core\Console\Command\Command;
use Stateforge\Scenario\Core\Console\Command\InstallScenarioCommand;
use Stateforge\Scenario\Core\Console\Exception\NotAllowedOptionsException;
use Stateforge\Scenario\Core\Console\Input;
use Stateforge\Scenario\Core\Console\Input\Option;
use Stateforge\Scenario\Core\Console\Input\Parser;
use Stateforge\Scenario\Core\Console\Input\Resolver;
use Stateforge\Scenario\Core\Contract\CliInput;
use Stateforge\Scenario\Core\Contract\CliOutput;
use Stateforge\Scenario\Core\PHPUnit\Configuration\ConfigFinder;
use Stateforge\Scenario\Core\PHPUnit\Configuration\Configurator;
use Stateforge\Scenario\Core\PHPUnit\Configuration\Configured;
use Stateforge\Scenario\Core\PHPUnit\Extension;
use Stateforge\Scenario\Core\Runtime\Application;
use Stateforge\Scenario\Core\Runtime\Application\ApplicationState;
use Stateforge\Scenario\Core\Tests\Unit\ApplicationMock;
use function file_get_contents;
use function file_put_contents;

#[CoversClass(InstallScenarioCommand::class)]
#[UsesClass(Application::class)]
#[UsesClass(ApplicationState::class)]
#[UsesClass(CliCommand::class)]
#[UsesClass(Command::class)]
#[UsesClass(ConfigFinder::class)]
#[UsesClass(Configured::class)]
#[UsesClass(Configurator::class)]
#[UsesClass(Extension::class)]
#[UsesClass(Input::class)]
#[UsesClass(NotAllowedOptionsException::class)]
#[UsesClass(Option::class)]
#[UsesClass(Parser::class)]
#[UsesClass(Resolver::class)]
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

    public function testRunReturnsErrorWhenResolveRejectsFooOption(): void
    {
        $input = new Input([
            'scenario',
            'install',
            '--foo=bar',
        ]);

        $output = $this->createMock(CliOutput::class);
        $output->expects(self::never())->method('success');
        $output->expects(self::never())->method('choice');
        $output->expects(self::never())->method('ask');
        $output->expects(self::once())
            ->method('error');

        self::assertSame(Command::Error, (new InstallScenarioCommand())->run($input, $output));
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
            ->with('Do you want to continue?', false)
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
            ->willReturnCallback(function (string $question, bool $default) use ($matcher) {
                self::assertFalse($default);
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
            ->willReturnCallback(function (string $question, bool $default) use ($matcher) {
                self::assertFalse($default);
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
