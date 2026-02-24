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

enum BackgroundColor: int
{
    case Black = 40;
    case Red = 41;
    case Green = 42;
    case Yellow = 103;
    case Blue = 104;
    case Magenta = 45;
    case Cyan = 46;
    case White = 47;
}
