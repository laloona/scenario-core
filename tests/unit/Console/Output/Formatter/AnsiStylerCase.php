<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Tests\Unit\Console\Output\Formatter;

use PHPUnit\Framework\TestCase;
use Scenario\Core\Console\Output\Theme\AnsiStyler;
use Scenario\Core\Console\Output\Theme\TerminalEnvironment;

abstract class AnsiStylerCase extends TestCase
{
    protected function styler(string $columsEnv = '180'): AnsiStyler
    {
        $terminal = self::createStub(TerminalEnvironment::class);
        $terminal->method('columnsEnv')->willReturn($columsEnv);
        $terminal->method('isTty')->willReturn(true);
        $terminal->method('noColorEnv')->willReturn(false);

        return new AnsiStyler($terminal);
    }
}
