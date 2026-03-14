<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Tests\Unit\Console\Output\Theme;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Scenario\Core\Console\Output\Theme\AnsiStyler;
use Scenario\Core\Console\Output\Theme\BackgroundColor;
use Scenario\Core\Console\Output\Theme\FontStyle;
use Scenario\Core\Console\Output\Theme\ForegroundColor;
use Scenario\Core\Console\Output\Theme\TerminalEnvironment;

#[CoversClass(AnsiStyler::class)]
#[UsesClass(BackgroundColor::class)]
#[UsesClass(ForegroundColor::class)]
#[UsesClass(FontStyle::class)]
#[Group('console')]
#[Small]
final class AnsiStylerTest extends TestCase
{
    public function testNoColorDisablesFormattingAndUsesMinimumWidth(): void
    {
        $terminal = self::createStub(TerminalEnvironment::class);
        $terminal->method('columnsEnv')->willReturn('80');
        $terminal->method('stdoutIsTty')->willReturn(true);
        $terminal->method('noColorEnv')->willReturn(true);

        $styler = new AnsiStyler($terminal);

        self::assertSame(150, $styler->outputWidth);
        self::assertSame(99, $styler->scaleWidth());
        self::assertSame(
            'Text',
            $styler->bgText('Text', BackgroundColor::Red, ForegroundColor::Green, FontStyle::Bold),
        );
    }

    public function testFormatsTextWhenColorsEnabledAndTtyUnknown(): void
    {
        $terminal = self::createStub(TerminalEnvironment::class);
        $terminal->method('columnsEnv')->willReturn('180');
        $terminal->method('stdoutIsTty')->willReturn(null);
        $terminal->method('noColorEnv')->willReturn(false);

        $result = new AnsiStyler($terminal)->bgText('X', BackgroundColor::Red, ForegroundColor::Green, FontStyle::Bold);
        self::assertSame("\033[41;32;1mX\033[0m", $result);
    }

    public function testTextUsesForegroundAndStyleOnly(): void
    {
        $terminal = self::createStub(TerminalEnvironment::class);
        $terminal->method('columnsEnv')->willReturn('180');
        $terminal->method('stdoutIsTty')->willReturn(true);
        $terminal->method('noColorEnv')->willReturn(false);

        $result = new AnsiStyler($terminal)->text('MyText', ForegroundColor::Blue, FontStyle::Underline);

        self::assertSame("\033[94;4mMyText\033[0m", $result);
    }

    public function testWidthUsesWindowsShellOutput(): void
    {
        $terminal = self::createStub(TerminalEnvironment::class);
        $terminal->method('osFamily')->willReturn('Windows');
        $terminal->method('shellExec')->willReturn('Columns: 180');
        $terminal->method('columnsEnv')->willReturn(null);
        $terminal->method('stdoutIsTty')->willReturn(true);
        $terminal->method('noColorEnv')->willReturn(true);

        self::assertSame(180, new AnsiStyler($terminal)->outputWidth);
    }

    public function testWidthFallsBackOnWindowsShellOutput(): void
    {
        $terminal = self::createStub(TerminalEnvironment::class);
        $terminal->method('osFamily')->willReturn('Windows');
        $terminal->method('shellExec')->willReturn('n/a');
        $terminal->method('columnsEnv')->willReturn(null);
        $terminal->method('stdoutIsTty')->willReturn(true);
        $terminal->method('noColorEnv')->willReturn(true);

        self::assertSame(150, new AnsiStyler($terminal)->outputWidth);
    }

    public function testWidthUsesUnixShellOutput(): void
    {
        $terminal = self::createStub(TerminalEnvironment::class);
        $terminal->method('osFamily')->willReturn('Linux');
        $terminal->method('shellExec')->willReturn("24 190\n");
        $terminal->method('columnsEnv')->willReturn(null);
        $terminal->method('stdoutIsTty')->willReturn(true);
        $terminal->method('noColorEnv')->willReturn(true);

        self::assertSame(190, new AnsiStyler($terminal)->outputWidth);
    }

    public function testWidthFallsBackOnUnixShellOutput(): void
    {
        $terminal = self::createStub(TerminalEnvironment::class);
        $terminal->method('osFamily')->willReturn('Windows');
        $terminal->method('shellExec')->willReturn(null);
        $terminal->method('columnsEnv')->willReturn(null);
        $terminal->method('stdoutIsTty')->willReturn(true);
        $terminal->method('noColorEnv')->willReturn(true);

        self::assertSame(150, new AnsiStyler($terminal)->outputWidth);
    }
}
