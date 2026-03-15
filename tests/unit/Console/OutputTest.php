<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Tests\Unit\Console;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Scenario\Core\Console\Output;
use Scenario\Core\Console\Output\Formatter\Box;
use Scenario\Core\Console\Output\Formatter\Confirm;
use Scenario\Core\Console\Output\Formatter\Table;
use Scenario\Core\Console\Output\Formatter\Text;
use Scenario\Core\Console\Output\TerminalEnvironment;
use Scenario\Core\Console\Output\TerminalIO;
use Scenario\Core\Console\Output\Theme\AnsiStyler;
use Scenario\Core\Console\Output\Theme\BoxType;

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
