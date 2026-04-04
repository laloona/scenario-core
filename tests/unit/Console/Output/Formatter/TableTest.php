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
use Stateforge\Scenario\Core\Console\Output\Formatter\Align;
use Stateforge\Scenario\Core\Console\Output\Formatter\AnsiString;
use Stateforge\Scenario\Core\Console\Output\Formatter\Table;
use Stateforge\Scenario\Core\Console\Output\Theme\AnsiStyler;
use Stateforge\Scenario\Core\Console\Output\Theme\FontStyle;
use function count;
use function implode;
use function preg_replace;
use function str_repeat;

#[CoversClass(Table::class)]
#[UsesClass(Align::class)]
#[UsesClass(AnsiString::class)]
#[UsesClass(AnsiStyler::class)]
#[UsesClass(FontStyle::class)]
#[Group('console')]
#[Small]
final class TableTest extends AnsiStylerCase
{
    public function testGenerateReturnsNullWhenEmpty(): void
    {
        self::assertNull(
            new Table($this->styler())->generate(null, []),
        );
    }

    public function testGenerateBuildsTableWithHeadersRowsAndBorder(): void
    {
        $rows = [
            ['Alice', '10'],
            ['Bob', null],
        ];
        $headers = ['Name', 'Score'];
        $lines = new Table($this->styler())
            ->generate($headers, $rows, [Align::Left, Align::Right], true);

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
        $lines = new Table($this->styler())
            ->generate(null, [['A', 'B']], null, false);

        self::assertNotNull($lines);
        self::assertCount(1, $lines);
        self::assertStringContainsString('A', $lines[0]);
        self::assertStringContainsString('B', $lines[0]);
    }

    public function testGenerateTruncatesCellsWhenWidthTooSmall(): void
    {
        $long = str_repeat('X', 80);
        $headers = ['HeaderOne', 'HeaderTwo', 'HeaderThree', 'HeaderFour'];
        $rows = [
            [$long, $long, $long, $long],
        ];

        $lines = new Table($this->styler('150'))
            ->generate($headers, $rows, [Align::Left, Align::Center], true);

        self::assertNotNull($lines);

        $body = $this->stripAnsi($lines[3] ?? implode("\n", $lines));
        self::assertStringContainsString('…', $body);
    }

    private function stripAnsi(string $value): string
    {
        return preg_replace('/\e\[[\d;]*m/', '', $value) ?? $value;
    }
}
