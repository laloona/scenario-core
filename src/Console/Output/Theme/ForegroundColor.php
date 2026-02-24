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

enum ForegroundColor: int
{
    case Black = 30;
    case Red = 91;
    case Green = 32;
    case Yellow = 33;
    case Blue = 94;
    case Magenta = 35;
    case Cyan = 36;
    case White = 97;
    case Grey = 90;
}
