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
use Scenario\Core\Console\Output\Formatter\Confirm;
use Scenario\Core\Console\Output\Formatter\Text;
use Scenario\Core\Console\Output\Theme\AnsiStyler;
use Scenario\Core\Console\Output\Theme\ForegroundColor;
use Scenario\Core\Tests\Files\FakeTerminalEnvironment;

#[CoversClass(Confirm::class)]
#[UsesClass(AnsiString::class)]
#[UsesClass(Text::class)]
#[UsesClass(AnsiStyler::class)]
#[UsesClass(ForegroundColor::class)]
#[Group('console')]
#[Small]
final class ConfirmTest extends TestCase
{
    public function testOptionsDefaultTrueHighlightsYes(): void
    {
        $confirm = new Confirm($this->styler());

        $result = $confirm->options(true);

        self::assertStringContainsString("\033[33mYes\033[0m", $result);
        self::assertStringContainsString("\033[33mno\033[0m", $result);
    }

    public function testOptionsDefaultFalseHighlightsNo(): void
    {
        $confirm = new Confirm($this->styler());

        $result = $confirm->options(false);

        self::assertStringContainsString("\033[33myes\033[0m", $result);
        self::assertStringContainsString("\033[33mNo\033[0m", $result);
    }

    public function testQuestionWrapsTextAndAppendsColon(): void
    {
        $confirm = new Confirm($this->styler());

        $result = $confirm->question('Proceed?');

        self::assertStringContainsString("\033[32mProceed?\033[0m", $result);
        self::assertStringContainsString("\033[32m:\033[0m", $result);
    }

    public function testErrorAddsPrefixAndOptions(): void
    {
        $confirm = new Confirm($this->styler());

        $result = $confirm->error();

        self::assertStringContainsString("\033[91mPlease answer with: \033[0m", $result);
        self::assertStringContainsString("\033[33mYes\033[0m", $result);
        self::assertStringContainsString("\033[33mno\033[0m", $result);
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
