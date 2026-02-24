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

use Scenario\Core\Console\Output\Theme\AnsiStyler;
use function explode;
use function intdiv;
use function max;
use function mb_strlen;
use function mb_substr;
use function preg_replace;
use function str_repeat;
use function wordwrap;

abstract class AnsiString
{
    final public function __construct(protected AnsiStyler $ansiStyler)
    {
    }

    final protected function ansiLength(string $string): int
    {
        return mb_strlen($this->ansiStrip($string));
    }

    final protected function ansiStrip(string $string): string
    {
        return preg_replace('/\e\[[\d;]*m/', '', $string) ?? $string;
    }

    final protected function padLeft(string $string, int $length): string
    {
        return $string . str_repeat(' ', max(0, $length - $this->ansiLength($string)));
    }

    final protected function padRight(string $string, int $length): string
    {
        return str_repeat(' ', max(0, $length - $this->ansiLength($string))) . $string;
    }

    final protected function padCenter(string $string, int $length): string
    {
        $pad = max(0, $length - $this->ansiLength($string));
        $left = intdiv($pad, 2);
        return str_repeat(' ', $left) . $string . str_repeat(' ', ($pad - $left));
    }

    final protected function truncate(string $string, int $maxLength): string
    {
        if ($this->ansiLength($string) <= $maxLength) {
            return $string;
        }

        $string = $this->ansiStrip($string);
        if (mb_strlen($string) <= $maxLength) {
            return mb_substr($string, 0, $maxLength);
        }

        $ellipsis = '…';
        return mb_substr($string, 0, max(0, $maxLength - mb_strlen($ellipsis))) . $ellipsis;
    }

    /**
     * @return string[]
     */
    final protected function wrap(string $string, int $maxLength): array
    {
        return explode(PHP_EOL, wordwrap($this->ansiStrip($string), $maxLength, PHP_EOL, true));
    }
}
