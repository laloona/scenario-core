<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Tests\Unit\Console;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Stateforge\Scenario\Core\Console\Output;
use Stateforge\Scenario\Core\Console\Output\Formatter\Align;
use Stateforge\Scenario\Core\Console\Output\Formatter\Box;
use Stateforge\Scenario\Core\Console\Output\Formatter\Confirm;
use Stateforge\Scenario\Core\Console\Output\Formatter\Table;
use Stateforge\Scenario\Core\Console\Output\Formatter\Text;
use Stateforge\Scenario\Core\Console\Output\TerminalEnvironment;
use Stateforge\Scenario\Core\Console\Output\TerminalIO;
use Stateforge\Scenario\Core\Console\Output\Theme\AnsiStyler;
use Stateforge\Scenario\Core\Console\Output\Theme\BoxType;
use const PHP_EOL;

#[CoversClass(Output::class)]
#[UsesClass(AnsiStyler::class)]
#[UsesClass(BoxType::class)]
#[UsesClass(Box::class)]
#[UsesClass(Confirm::class)]
#[UsesClass(Table::class)]
#[UsesClass(Text::class)]
#[Group('console')]
#[Small]
final class OutputTest extends TestCase
{
    public function testWriteAndWritelnForwardStringsAndArraysToTerminal(): void
    {
        $terminalIO = $this->createMock(TerminalIO::class);

        $matcher = self::exactly(4);
        $terminalIO->expects($matcher)
            ->method('write')
            ->willReturnCallback(function (string $line) use ($matcher): void {
                match($matcher->numberOfInvocations()) {
                    1 => self::assertSame('first', $line),
                    2 => self::assertSame('second', $line),
                    3 => self::assertSame('third' . PHP_EOL, $line),
                    4 => self::assertSame('fourth' . PHP_EOL, $line),
                    default => '',
                };
            });

        $output = new Output(new AnsiStyler($this->getTerminalStub()), $terminalIO);
        $output->write(['first', 'second']);
        $output->writeln(['third', 'fourth']);
    }

    public function testAskRetriesUntilValidatorAcceptsInput(): void
    {
        $terminalIO = $this->createMock(TerminalIO::class);
        $terminalIO->expects(self::exactly(2))
            ->method('read')
            ->willReturnOnConsecutiveCalls('wrong', 'accepted');

        $matcher = self::exactly(9);
        $terminalIO->expects($matcher)
            ->method('write')
            ->willReturnCallback(function (string $line) use ($matcher): void {
                match($matcher->numberOfInvocations()) {
                    3 => self::assertStringContainsString('Enter value', $line),
                    6 => self::assertStringContainsString('[default]', $line),
                    7, 9 => self::assertStringContainsString('>', $line),
                    8 => self::assertStringContainsString('Input was invalid, please try again:', $line),
                    default => '',
                };
            });

        $result = new Output(new AnsiStyler($this->getTerminalStub()), $terminalIO)
            ->ask('Enter value', 'default', static fn (string $input): bool => $input === 'accepted');

        self::assertSame('accepted', $result);
    }

    public function testConfirmRepeatsUntilValidAnswerAndUsesDefaultOnEmptyInput(): void
    {
        $terminalIO = $this->createMock(TerminalIO::class);
        $terminalIO->expects(self::exactly(2))
            ->method('read')
            ->willReturnOnConsecutiveCalls('maybe', '');

        $matcher = self::exactly(5);
        $terminalIO->expects($matcher)
            ->method('write')
            ->willReturnCallback(function (string $line) use ($matcher): void {
                match($matcher->numberOfInvocations()) {
                    1, 4 => self::assertStringContainsString('Continue? [Yes / no]', $line),
                    2, 5 => self::assertStringContainsString('>', $line),
                    3 => self::assertStringContainsString('Please answer with:  [Yes / no]', $line),
                    default => '',
                };
            });

        $result = new Output(new AnsiStyler($this->getTerminalStub()), $terminalIO)
            ->confirm('Continue?', true);

        self::assertTrue($result);
    }

    public function testChoiceShowsOptionsAndRetriesUntilValidSelection(): void
    {
        $terminalIO = $this->createMock(TerminalIO::class);
        $terminalIO->expects(self::exactly(2))
            ->method('read')
            ->willReturnOnConsecutiveCalls('3', '0');

        $matcher = self::exactly(12);
        $terminalIO->expects($matcher)
            ->method('write')
            ->willReturnCallback(function (string $line) use ($matcher): void {
                match($matcher->numberOfInvocations()) {
                    3 => self::assertStringContainsString('Select one', $line),
                    6 => self::assertStringContainsString('Please select one of the following:', $line),
                    8 => self::assertStringContainsString('(0) first', $line),
                    9 => self::assertStringContainsString('(1) second', $line),
                    10, 12 => self::assertStringContainsString('>', $line),
                    11 => self::assertStringContainsString('Please insert a number from 0 to 1:', $line),
                    default => '',
                };
            });

        $result = new Output(new AnsiStyler($this->getTerminalStub()), $terminalIO)
            ->choice('Select one', ['first', 'second']);

        self::assertSame('0', $result);
    }

    public function testChoiceReturnsDefaultWhenInputIsEmpty(): void
    {
        $terminalIO = $this->createMock(TerminalIO::class);
        $terminalIO->expects(self::once())
            ->method('read')
            ->willReturn('');

        $matcher = self::exactly(10);
        $terminalIO->expects($matcher)
            ->method('write')
            ->willReturnCallback(function (string $line) use ($matcher): void {
                match($matcher->numberOfInvocations()) {
                    3 => self::assertStringContainsString('Choose one', $line),
                    6 => self::assertStringContainsString('Please select one of the following:', $line),
                    7 => self::assertStringContainsString('default (second)', $line),
                    8 => self::assertStringContainsString('(0) first', $line),
                    9 => self::assertStringContainsString('(1) second', $line),
                    10 => self::assertStringContainsString('>', $line),
                    default => '',
                };
            });

        $result = new Output(new AnsiStyler($this->getTerminalStub()), $terminalIO)
            ->choice('Choose one', ['first', 'second'], 'second');

        self::assertSame('second', $result);
    }

    public function testHeadlineWritesHeadlineAndUnderlineWithSpacing(): void
    {
        $terminalIO = $this->createMock(TerminalIO::class);

        $matcher = self::exactly(4);
        $terminalIO->expects($matcher)
            ->method('write')
            ->willReturnCallback(function (string $line) use ($matcher): void {
                match($matcher->numberOfInvocations()) {
                    1, 4 => self::assertSame(PHP_EOL, $line),
                    2 => self::assertSame('Important' . PHP_EOL, $line),
                    3 => self::assertSame('---------' . PHP_EOL, $line),
                    default => '',
                };
            });

        (new Output(new AnsiStyler($this->getTerminalStub()), $terminalIO))
            ->headline('Important');
    }

    public function testSuccessWrapsFormattedBoxWithBlankLines(): void
    {
        $terminalIO = $this->createMock(TerminalIO::class);

        $matcher = self::exactly(5);
        $terminalIO->expects($matcher)
            ->method('write')
            ->willReturnCallback(function (string $line) use ($matcher): void {
                match($matcher->numberOfInvocations()) {
                    1, 5 => self::assertSame(PHP_EOL, $line),
                    2, 4 => self::assertStringContainsString(PHP_EOL, $line),
                    3 => self::assertStringContainsString('[OK] Done', $line),
                    default => '',
                };
            });

        (new Output(new AnsiStyler($this->getTerminalStub()), $terminalIO))
            ->success('Done');
    }

    public function testTextWritesPlainTextWithTrailingLineBreak(): void
    {
        $terminalIO = $this->createMock(TerminalIO::class);
        $terminalIO->expects(self::once())
            ->method('write')
            ->with('Hello' . PHP_EOL);

        (new Output(new AnsiStyler($this->getTerminalStub()), $terminalIO))
            ->text('Hello');
    }

    public function testTableWritesGeneratedTableBetweenBlankLines(): void
    {
        $terminalIO = $this->createMock(TerminalIO::class);

        $matcher = self::exactly(7);
        $terminalIO->expects($matcher)
            ->method('write')
            ->willReturnCallback(function (string $line) use ($matcher): void {
                match($matcher->numberOfInvocations()) {
                    1, 7 => self::assertSame(PHP_EOL, $line),
                    2, 4, 6 => self::assertStringContainsString('─', $line),
                    3 => self::assertMatchesRegularExpression('/Name.*Score/', $line),
                    5 => self::assertMatchesRegularExpression('/Alice.*10/', $line),
                    default => '',
                };
            });

        (new Output(new AnsiStyler($this->getTerminalStub()), $terminalIO))
            ->table(['Name', 'Score'], [['Alice', '10']], [Align::Left, Align::Right]);
    }

    public function testInfoWritesMessageBetweenBlankLines(): void
    {
        $terminalIO = $this->createMock(TerminalIO::class);

        $matcher = self::exactly(3);
        $terminalIO->expects($matcher)
            ->method('write')
            ->willReturnCallback(function (string $line) use ($matcher): void {
                match($matcher->numberOfInvocations()) {
                    1, 3 => self::assertSame(PHP_EOL, $line),
                    2 => self::assertSame('Heads up' . PHP_EOL, $line),
                    default => '',
                };
            });

        (new Output(new AnsiStyler($this->getTerminalStub()), $terminalIO))
            ->info('Heads up');
    }

    public function testWarnWrapsFormattedBoxWithBlankLines(): void
    {
        $terminalIO = $this->createMock(TerminalIO::class);

        $matcher = self::exactly(5);
        $terminalIO->expects($matcher)
            ->method('write')
            ->willReturnCallback(function (string $line) use ($matcher): void {
                match($matcher->numberOfInvocations()) {
                    1, 5 => self::assertSame(PHP_EOL, $line),
                    2, 4 => self::assertStringContainsString(PHP_EOL, $line),
                    3 => self::assertStringContainsString('[WARNING] Careful', $line),
                    default => '',
                };
            });

        (new Output(new AnsiStyler($this->getTerminalStub()), $terminalIO))
            ->warn('Careful');
    }

    public function testErrorWrapsFormattedBoxWithBlankLines(): void
    {
        $terminalIO = $this->createMock(TerminalIO::class);

        $matcher = self::exactly(5);
        $terminalIO->expects($matcher)
            ->method('write')
            ->willReturnCallback(function (string $line) use ($matcher): void {
                match($matcher->numberOfInvocations()) {
                    1, 5 => self::assertSame(PHP_EOL, $line),
                    2, 4 => self::assertStringContainsString(PHP_EOL, $line),
                    3 => self::assertStringContainsString('[ERROR] Broken', $line),
                    default => '',
                };
            });

        (new Output(new AnsiStyler($this->getTerminalStub()), $terminalIO))
            ->error('Broken');
    }

    public function testQuestionWrapsFormattedBoxWithBlankLines(): void
    {
        $terminalIO = $this->createMock(TerminalIO::class);

        $matcher = self::exactly(5);
        $terminalIO->expects($matcher)
            ->method('write')
            ->willReturnCallback(function (string $line) use ($matcher): void {
                match($matcher->numberOfInvocations()) {
                    1, 5 => self::assertSame(PHP_EOL, $line),
                    2, 4 => self::assertStringContainsString(PHP_EOL, $line),
                    3 => self::assertStringContainsString('Need input?', $line),
                    default => '',
                };
            });

        (new Output(new AnsiStyler($this->getTerminalStub()), $terminalIO))
            ->question('Need input?');
    }

    private function getTerminalStub(): TerminalEnvironment
    {
        $terminal = self::createStub(TerminalEnvironment::class);
        $terminal->method('noColorEnv')->willReturn(true);
        $terminal->method('isTty')->willReturn(false);
        $terminal->method('columnsEnv')->willReturn('180');
        $terminal->method('shellExec')->willReturn(null);
        $terminal->method('osFamily')->willReturn('Linux');

        return $terminal;
    }
}
