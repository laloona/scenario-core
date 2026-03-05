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

use Scenario\Core\Console\Output\Theme\ForegroundColor;

final class Confirm extends AnsiString
{
    public function question(string $question, bool $default = true): string
    {
        $text = new Text($this->ansiStyler);
        return $text->info($question) .
            $this->options($default) .
            $text->info(':');
    }

    public function options(bool $default = true): string
    {
        $text = new Text($this->ansiStyler);
        return $text->text(' [', null) .
            $text->text($default === true ? 'Yes' : 'yes', ForegroundColor::Yellow) .
            $text->text(' / ', null) .
            $text->text($default === true ? 'no' : 'No', ForegroundColor::Yellow) .
            $text->text(']', null);
    }

    public function error(bool $default = true): string
    {
        return (new Text($this->ansiStyler))->text('Please answer with: ', ForegroundColor::Red) .
            $this->options($default);
    }
}
