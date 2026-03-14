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
use Scenario\Core\Console\Output\Formatter\AnsiString;
use Scenario\Core\Console\Output\Formatter\Box;
use Scenario\Core\Console\Output\Theme\AnsiStyler;
use Scenario\Core\Console\Output\Theme\BackgroundColor;
use Scenario\Core\Console\Output\Theme\BoxType;
use Scenario\Core\Console\Output\Theme\FontStyle;
use Scenario\Core\Console\Output\Theme\ForegroundColor;

#[CoversClass(Box::class)]
#[UsesClass(AnsiString::class)]
#[UsesClass(AnsiStyler::class)]
#[UsesClass(BoxType::class)]
#[UsesClass(BackgroundColor::class)]
#[UsesClass(ForegroundColor::class)]
#[UsesClass(FontStyle::class)]
#[Group('console')]
#[Small]
final class BoxCase extends AnsiStylerCase
{
    public function testWarnGeneratesColoredBoxWithPrefix(): void
    {
        $box = new Box($this->styler());
        $lines = $box->warn('Something went wrong in the subsystem');

        self::assertGreaterThanOrEqual(3, count($lines));
        self::assertSame($lines[0], $lines[count($lines) - 1]);
        self::assertStringContainsString("\033[103;30;1m", $lines[1]);
        self::assertStringContainsString('[WARNING] ', $lines[1]);
    }

    public function testQuestionHasNoPrefixAndRespectsWidthFactor(): void
    {
        $box = new Box($this->styler());
        $lines = $box->generate(BoxType::Question, 'Proceed', 0.2);

        self::assertGreaterThanOrEqual(3, count($lines));

        $plain = preg_replace('/\e\[[\d;]*m/', '', $lines[1]) ?? $lines[1];
        self::assertStringNotContainsString('[QUESTION]', $plain);
        self::assertStringContainsString('Proceed', $plain);
        self::assertSame(36, strlen($plain));
    }

    public function testErrorSuccessQuestionHelpersRenderExpectedPrefixes(): void
    {
        $box = new Box($this->styler());

        $error = $box->error('E');
        $success = $box->success('S');
        $question = $box->question('Q');

        self::assertStringContainsString('[ERROR] ', $error[1]);
        self::assertStringContainsString('[OK] ', $success[1]);

        $plainQuestion = preg_replace('/\e\[[\d;]*m/', '', $question[1]) ?? $question[1];
        self::assertStringNotContainsString('[', $plainQuestion);
        self::assertStringContainsString('Q', $plainQuestion);
    }
}
