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
use function implode;
use function is_string;
use function max;
use function preg_match;
use function trim;

final class AnsiStyler
{
    private readonly bool $useColors;

    public readonly int $outputWidth;

    public function __construct(private readonly TerminalEnvironment $environment = new SystemTerminalEnvironment())
    {
        $this->useColors = $this->useColors();
        $this->outputWidth = $this->outputWidth();
    }

    private function useColors(): bool
    {
        if ($this->environment->noColorEnv() === true) {
            return false;
        }

        return $this->environment->stdoutIsTty() ?? true;
    }

    private function outputWidth(): int
    {
        $width = 100;

        $cols = $this->environment->columnsEnv();
        if (is_string($cols)
            && ctype_digit($cols)) {
            $width = (int)$cols;
        } else {
            $width = $this->shellWidth($width);
        }

        return max(150, min(200, $width));
    }

    private function shellWidth(int $fallback): int
    {
        if ($this->environment->osFamily() === 'Windows') {
            $out = $this->environment->shellExec('mode CON');
            if (is_string($out) === true
                && preg_match('/Columns:\s+(\d+)/', $out, $m) === 1) {
                return (int)$m[1];
            }

            return $fallback;
        }

        $out = $this->environment->shellExec('stty size 2>/dev/null');
        if (is_string($out) === true
            && preg_match('/\d+\s+(\d+)/', trim($out), $m) === 1) {
            return (int) $m[1];
        }

        return $fallback;
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
