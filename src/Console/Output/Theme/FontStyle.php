<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Console\Output\Theme;

enum FontStyle: int
{
    case Reset = 0;
    case Bold = 1;
    case Dim = 2;
    case Underline = 4;
}
