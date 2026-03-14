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
use Scenario\Core\Console\Output\Formatter\Confirm;
use Scenario\Core\Console\Output\Formatter\Text;
use Scenario\Core\Console\Output\Theme\AnsiStyler;
use Scenario\Core\Console\Output\Theme\ForegroundColor;

#[CoversClass(Confirm::class)]
#[UsesClass(AnsiString::class)]
#[UsesClass(AnsiStyler::class)]
#[UsesClass(ForegroundColor::class)]
#[UsesClass(Text::class)]
#[Group('console')]
#[Small]
final class ConfirmCase extends AnsiStylerCase
{
    public function testOptionsDefaultTrueHighlightsYes(): void
    {
        $result = new Confirm($this->styler())->options(true);

        self::assertStringContainsString("\033[33mYes\033[0m", $result);
        self::assertStringContainsString("\033[33mno\033[0m", $result);
    }

    public function testOptionsDefaultFalseHighlightsNo(): void
    {
        $result = new Confirm($this->styler())->options(false);

        self::assertStringContainsString("\033[33myes\033[0m", $result);
        self::assertStringContainsString("\033[33mNo\033[0m", $result);
    }

    public function testQuestionWrapsTextAndAppendsColon(): void
    {
        $result = new Confirm($this->styler())->question('Proceed?');

        self::assertStringContainsString("\033[32mProceed?\033[0m", $result);
        self::assertStringContainsString("\033[32m:\033[0m", $result);
    }

    public function testErrorAddsPrefixAndOptions(): void
    {
        $result = new Confirm($this->styler())->error();

        self::assertStringContainsString("\033[91mPlease answer with: \033[0m", $result);
        self::assertStringContainsString("\033[33mYes\033[0m", $result);
        self::assertStringContainsString("\033[33mno\033[0m", $result);
    }
}
