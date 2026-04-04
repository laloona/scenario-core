<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Console\Output\Formatter;

use Stateforge\Scenario\Core\Console\Output\Theme\ForegroundColor;

final class Text extends AnsiString
{
    public function text(string $text, ?ForegroundColor $foregroundColor): string
    {
        return $this->ansiStyler->text($text, $foregroundColor, null);
    }

    public function info(string $text): string
    {
        return $this->ansiStyler->text($text, ForegroundColor::Green, null);
    }

    public function comment(string $text): string
    {
        return $this->ansiStyler->text($text, ForegroundColor::Grey, null);
    }
}
