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
use Scenario\Core\Tests\Files\FakeTerminalEnvironment;

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
        $styler = new AnsiStyler(new FakeTerminalEnvironment(
            noColor: true,
            stdoutIsTty: true,
            columnsEnv: '80',
        ));

        self::assertSame(150, $styler->outputWidth);
        self::assertSame(99, $styler->scaleWidth());
        self::assertSame(
            'Text',
            $styler->bgText('Text', BackgroundColor::Red, ForegroundColor::Green, FontStyle::Bold),
        );
    }

    public function testFormatsTextWhenColorsEnabledAndTtyUnknown(): void
    {
        $styler = new AnsiStyler(new FakeTerminalEnvironment(
            noColor: false,
            stdoutIsTty: null,
            columnsEnv: '180',
        ));

        $result = $styler->bgText('X', BackgroundColor::Red, ForegroundColor::Green, FontStyle::Bold);
        self::assertSame("\033[41;32;1mX\033[0m", $result);
    }

    public function testTextUsesForegroundAndStyleOnly(): void
    {
        $styler = new AnsiStyler(new FakeTerminalEnvironment(
            noColor: false,
            stdoutIsTty: true,
            columnsEnv: '180',
        ));

        $result = $styler->text('Hi', ForegroundColor::Blue, FontStyle::Underline);
        self::assertSame("\033[94;4mHi\033[0m", $result);
    }

    public function testWidthUsesWindowsShellOutput(): void
    {
        $styler = new AnsiStyler(new FakeTerminalEnvironment(
            noColor: true,
            stdoutIsTty: true,
            columnsEnv: null,
            osFamily: 'Windows',
            shellOutput: 'Columns: 180',
        ));

        self::assertSame(180, $styler->outputWidth);
    }

    public function testWidthFallsBackOnWindowsShellOutput(): void
    {
        $styler = new AnsiStyler(new FakeTerminalEnvironment(
            noColor: true,
            stdoutIsTty: true,
            columnsEnv: null,
            osFamily: 'Windows',
            shellOutput: 'n/a',
        ));

        self::assertSame(150, $styler->outputWidth);
    }

    public function testWidthUsesUnixShellOutput(): void
    {
        $styler = new AnsiStyler(new FakeTerminalEnvironment(
            noColor: true,
            stdoutIsTty: true,
            columnsEnv: null,
            osFamily: 'Linux',
            shellOutput: "24 190\n",
        ));

        self::assertSame(190, $styler->outputWidth);
    }

    public function testWidthFallsBackOnUnixShellOutput(): void
    {
        $styler = new AnsiStyler(new FakeTerminalEnvironment(
            noColor: true,
            stdoutIsTty: true,
            columnsEnv: null,
            osFamily: 'Linux',
            shellOutput: null,
        ));

        self::assertSame(150, $styler->outputWidth);
    }
}
