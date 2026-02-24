<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Console\Output\Theme;

use function ctype_digit;
use function function_exists;
use function getenv;
use function implode;
use function is_string;
use function max;
use function preg_match;
use function shell_exec;
use function trim;

final class AnsiStyler
{
    private readonly bool $useColors;

    public readonly int $outputWidth;

    public function __construct()
    {
        $this->useColors = $this->useColors();
        $this->outputWidth = $this->outputWidth();
    }

    private function useColors(): bool
    {
        if (getenv('NO_COLOR') !== false) {
            return false;
        }

        if (function_exists('stream_isatty')) {
            return @stream_isatty(STDOUT);
        }

        if (function_exists('posix_isatty')) {
            return @posix_isatty(STDOUT);
        }

        return true;
    }

    private function outputWidth(): int
    {
        $width = 100;

        $cols = getenv('COLUMNS');
        if (is_string($cols)
            && ctype_digit($cols)) {
            $width = (int) $cols;
        } else {
            $out = @shell_exec('stty size 2>/dev/null');
            if (is_string($out) === true
                && preg_match('/\d+\s+(\d+)/', trim($out), $m) === 1) {
                $width = (int) $m[1];
            }
        }

        return max(60, min(200, $width));
    }

    public function scaleWidth(float $widthFactor = 0.66): int
    {
        return max(30, (int) floor($this->outputWidth * $widthFactor));
    }

    public function bgText(
        string $text,
        ?BackgroundColor $backgroundColor,
        ?ForegroundColor $foregroundColor,
        ?FontStyle $fontStyle,
    ): string {
        if ($this->useColors === false
            || ($backgroundColor === null
                && $foregroundColor === null
                && $fontStyle === null)) {
            return $text;
        }

        $colors = [];
        if ($backgroundColor !== null) {
            $colors[] = $backgroundColor->value;
        }
        if ($foregroundColor !== null) {
            $colors[] = $foregroundColor->value;
        }
        if ($fontStyle !== null) {
            $colors[] = $fontStyle->value;
        }

        return "\033[" . implode(';', $colors) . 'm' . $text . "\033[0m";
    }

    public function text(
        string $text,
        ?ForegroundColor $foregroundColor,
        ?FontStyle $fontStyle,
    ): string {
        return $this->bgText($text, null, $foregroundColor, $fontStyle);
    }
}
