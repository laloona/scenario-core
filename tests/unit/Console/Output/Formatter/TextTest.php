<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Tests\Unit\Console\Output\Formatter;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Scenario\Core\Console\Output\Formatter\AnsiString;
use Scenario\Core\Console\Output\Formatter\Text;
use Scenario\Core\Console\Output\Theme\AnsiStyler;
use Scenario\Core\Console\Output\Theme\ForegroundColor;
use Scenario\Core\Tests\Files\FakeTerminalEnvironment;

#[CoversClass(Text::class)]
#[UsesClass(AnsiString::class)]
#[UsesClass(AnsiStyler::class)]
#[UsesClass(ForegroundColor::class)]
#[Group('console')]
#[Small]
final class TextTest extends TestCase
{
    public function testTextWithoutColorReturnsPlainText(): void
    {
        $text = new Text($this->styler());

        self::assertSame('Hello', $text->text('Hello', null));
    }

    public function testInfoUsesGreen(): void
    {
        $text = new Text($this->styler());

        self::assertSame("\033[32mInfo\033[0m", $text->info('Info'));
    }

    public function testCommentUsesGrey(): void
    {
        $text = new Text($this->styler());

        self::assertSame("\033[90mNote\033[0m", $text->comment('Note'));
    }

    public function testTextUsesProvidedForegroundColor(): void
    {
        $text = new Text($this->styler());

        self::assertSame("\033[94mHi\033[0m", $text->text('Hi', ForegroundColor::Blue));
    }

    private function styler(): AnsiStyler
    {
        return new AnsiStyler(new FakeTerminalEnvironment(
            noColor: false,
            stdoutIsTty: true,
            columnsEnv: '180',
        ));
    }
}
