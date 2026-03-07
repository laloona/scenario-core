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

enum BoxType: string
{
    case Error = 'error';
    case Success = 'success';
    case Warn = 'warn';
    case Question = 'question';
    case Note = 'note';

    public function prefix(): ?string
    {
        return match($this) {
            self::Error => '[ERROR] ',
            self::Success => '[OK] ',
            self::Warn => '[WARNING] ',
            self::Question => null,
            self::Note => null,
        };
    }

    public function background(): ?BackgroundColor
    {
        return match($this) {
            self::Error => BackgroundColor::Red,
            self::Success => BackgroundColor::Green,
            self::Warn => BackgroundColor::Yellow,
            self::Question => BackgroundColor::Blue,
            self::Note => null,
        };
    }

    public function foreground(): ?ForegroundColor
    {
        return match($this) {
            self::Error => ForegroundColor::White,
            self::Success => ForegroundColor::White,
            self::Warn => ForegroundColor::Black,
            self::Question => ForegroundColor::White,
            self::Note => null,
        };
    }
}
