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
use Scenario\Core\Console\Output\Formatter\Align;
use Scenario\Core\Console\Output\Formatter\AnsiString;
use Scenario\Core\Console\Output\Formatter\Table;
use Scenario\Core\Console\Output\Theme\AnsiStyler;
use Scenario\Core\Console\Output\Theme\FontStyle;
use Scenario\Core\Tests\Files\FakeTerminalEnvironment;

#[CoversClass(Table::class)]
#[UsesClass(AnsiString::class)]
#[UsesClass(Align::class)]
#[UsesClass(AnsiStyler::class)]
#[UsesClass(FontStyle::class)]
#[Group('console')]
#[Small]
final class TableTest extends TestCase
{
    public function testGenerateReturnsNullWhenEmpty(): void
    {
        $table = new Table($this->styler());

        self::assertNull($table->generate(null, []));
    }

    public function testGenerateBuildsTableWithHeadersRowsAndBorder(): void
    {
        $table = new Table($this->styler());

        $rows = [
            ['Alice', '10'],
            ['Bob', null],
        ];
        $headers = ['Name', 'Score'];
        $lines = $table->generate($headers, $rows, [Align::Left, Align::Right], true);

        self::assertNotNull($lines);
        self::assertGreaterThanOrEqual(4, count($lines));

        $header = $this->stripAnsi($lines[1]);
        $firstRow = $this->stripAnsi($lines[3]);
        $secondRow = $this->stripAnsi($lines[4]);

        self::assertStringContainsString('Name', $header);
        self::assertStringContainsString('Score', $header);
        self::assertStringContainsString('Alice', $firstRow);
        self::assertStringContainsString('10', $firstRow);
        self::assertStringContainsString('Bob', $secondRow);
    }

    public function testGenerateWithoutBorder(): void
    {
        $table = new Table($this->styler());

        $lines = $table->generate(null, [['A', 'B']], null, false);

        self::assertNotNull($lines);
        self::assertCount(1, $lines);
        self::assertStringContainsString('A', $lines[0]);
        self::assertStringContainsString('B', $lines[0]);
    }

    public function testGenerateTruncatesCellsWhenWidthTooSmall(): void
    {
        $table = new Table($this->smallStyler());

        $long = str_repeat('X', 80);
        $headers = ['HeaderOne', 'HeaderTwo', 'HeaderThree', 'HeaderFour'];
        $rows = [
            [$long, $long, $long, $long],
        ];

        $lines = $table->generate($headers, $rows, [Align::Left, Align::Center], true);

        self::assertNotNull($lines);
        $body = $this->stripAnsi($lines[3] ?? implode("\n", $lines));
        self::assertStringContainsString('…', $body);
    }

    private function stripAnsi(string $value): string
    {
        return preg_replace('/\e\[[\d;]*m/', '', $value) ?? $value;
    }

    private function styler(): AnsiStyler
    {
        return new AnsiStyler(new FakeTerminalEnvironment(
            noColor: false,
            stdoutIsTty: true,
            columnsEnv: '180',
        ));
    }

    private function smallStyler(): AnsiStyler
    {
        return new AnsiStyler(new FakeTerminalEnvironment(
            noColor: false,
            stdoutIsTty: true,
            columnsEnv: '150',
        ));
    }
}
