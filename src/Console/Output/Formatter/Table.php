<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Console\Output\Formatter;

use Scenario\Core\Console\Output\Theme\FontStyle;

final class Table extends AnsiString
{
    /**
     * @param list<string>|null $headers
     * @param list<list<string|null>> $rows
     * @param list<'left'|'right'|'center'>|null $align
     * @return list<string>|null
     */
    public function generate(?array $headers, array $rows, ?array $align = null, bool $showBorder = true): ?array
    {
        if (($headers === null
            || count($headers) === 0)
            && count($rows) === 0) {
            return null;
        }

        $sizes = $this->calculateColumnSizes($headers, $rows);
        if ($headers !== null) {
            foreach ($headers as $i => $header) {
                $headers[$i] = $this->ansiStyler->text($header, null, FontStyle::Bold);
            }

            array_unshift($rows, $headers);
        }

        // all rows need the same size
        foreach ($rows as $i => $row) {
            foreach ($row as $j => $cell) {
                $row[$j] = $cell ?? '';
            }
            $rows[$i] = array_pad($row, count($sizes), '');
        }

        $border = $showBorder === true ? ' ' . $this->border($sizes, ' ') . ' ' : null;

        $table = [];
        if ($border !== null) {
            $table[] = $border;
        }
        foreach ($rows as $i => $row) {
            $table[] = $this->row($row, $sizes, $align ?? []);
            if ($border !== null
                && $headers !== null
                && $i === 0) {
                $table[] = $border;
            }
        }
        if ($border !== null) {
            $table[] = $border;
        }

        return $table;
    }

    /**
     * @param list<string>|null $headers
     * @param list<list<string|null>> $rows
     * @return list<int>
     */
    private function calculateColumnSizes(?array $headers, array $rows): array
    {
        // get cols
        $cols = count($headers ?? []);
        foreach ($rows as $row) {
            $cols = max($cols, count($row));
        }

        $sizes = array_fill(0, $cols, 0);
        if ($headers !== null) {
            array_unshift($rows, $headers);
        }
        foreach ($rows as $row) {
            foreach ($row as $i => $col) {
                $sizes[$i] = max($sizes[$i], $this->ansiLength($col ?? ''));
            }
        }

        // Fit to output width including padding
        $total = array_sum($sizes) + ($cols * 3) + 1;
        $over = $total - $this->ansiStyler->outputWidth;
        foreach ($sizes as $i => $size) {
            if ($over <= 0) {
                break;
            }
            $min = min(6, $size);
            if ($size > $min) {
                $dec = min($over, $size - $min);
                $sizes[$i] -= $dec;
                $over -= $dec;
            }
        }

        return array_values($sizes);
    }

    /**
     * @param list<int> $widths
     */
    private function border(array $widths, string $seperator): string
    {
        $cols = [];
        foreach ($widths as $width) {
            $cols[] = str_repeat('─', $width + 2);
        }
        return implode($seperator, $cols);
    }

    /**
     * @param list<string|null> $cells
     * @param list<int> $sizes
     * @param list<'left'|'right'|'center'> $align
     */
    private function row(array $cells, array $sizes, array $align): string
    {
        $out = ' ';
        foreach ($cells as $i => $cell) {
            $cell = $this->truncate((string)$cell, $sizes[$i]);

            $out .= ' ' . match ($align[$i] ?? Align::Left) {
                Align::Right => $this->padRight($cell, $sizes[$i]),
                Align::Center => $this->padCenter($cell, $sizes[$i]),
                default => $this->padLeft($cell, $sizes[$i]),
            } . '  ';
        }
        return $out;
    }
}
