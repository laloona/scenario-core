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

use Stateforge\Scenario\Core\Console\Output\Theme\BoxType;
use Stateforge\Scenario\Core\Console\Output\Theme\FontStyle;
use function array_push;
use function array_unshift;
use function array_values;
use function sprintf;
use function str_repeat;

final class Box extends AnsiString
{
    /**
     * @return list<string>
     */
    public function warn(string $message): array
    {
        return $this->generate(BoxType::Warn, $message);
    }

    /**
     * @return list<string>
     */
    public function error(string $message): array
    {
        return $this->generate(BoxType::Error, $message);
    }

    /**
     * @return list<string>
     */
    public function success(string $message): array
    {
        return $this->generate(BoxType::Success, $message);
    }

    /**
     * @return list<string>
     */
    public function question(string $message): array
    {
        return $this->generate(BoxType::Question, $message);
    }

    /**
     * @return list<string>
     */
    public function generate(
        BoxType $type,
        string $message,
        float $widthFactor = 0.66,
    ): array {
        $boxWidth = $this->ansiStyler->scaleWidth($widthFactor);

        $messages = $this->wrap(
            sprintf(
                '  %s%s  ',
                $type->prefix(),
                $message,
            ),
            $boxWidth - 5,
        );
        foreach ($messages as $i => $wrapped) {
            $messages[$i] = $this->ansiStyler->bgText($this->padLeft('  ' . $wrapped, $boxWidth), $type->background(), $type->foreground(), FontStyle::Bold);
        }

        $empty = $this->ansiStyler->bgText(str_repeat(' ', $boxWidth), $type->background(), $type->foreground(), null);
        array_unshift($messages, $empty);
        array_push($messages, $empty);

        return array_values($messages);
    }
}
