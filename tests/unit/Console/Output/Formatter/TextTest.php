<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Tests\Unit\Console\Output\Formatter;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use Stateforge\Scenario\Core\Console\Output\Formatter\AnsiString;
use Stateforge\Scenario\Core\Console\Output\Formatter\Text;
use Stateforge\Scenario\Core\Console\Output\Theme\AnsiStyler;
use Stateforge\Scenario\Core\Console\Output\Theme\ForegroundColor;

#[CoversClass(Text::class)]
#[UsesClass(AnsiString::class)]
#[UsesClass(AnsiStyler::class)]
#[UsesClass(ForegroundColor::class)]
#[Group('console')]
#[Small]
final class TextTest extends AnsiStylerCase
{
    public function testTextWithoutColorReturnsPlainText(): void
    {
        self::assertSame(
            'Hello',
            new Text($this->styler())->text('Hello', null),
        );
    }

    public function testInfoUsesGreen(): void
    {
        self::assertSame(
            "\033[32mInfo\033[0m",
            new Text($this->styler())->info('Info'),
        );
    }

    public function testCommentUsesGrey(): void
    {
        self::assertSame(
            "\033[90mNote\033[0m",
            new Text($this->styler())->comment('Note'),
        );
    }

    public function testTextUsesProvidedForegroundColor(): void
    {
        self::assertSame(
            "\033[94mHi\033[0m",
            new Text($this->styler())->text('Hi', ForegroundColor::Blue),
        );
    }
}
