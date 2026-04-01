<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Console\Output;

use function fgets;
use function fwrite;
use function rtrim;
use const STDIN;
use const STDOUT;

final class NativeTerminalIO implements TerminalIO
{
    public function read(): string
    {
        $line = fgets(STDIN);

        return $line === false
            ? ''
            : rtrim($line, "\r\n");
    }

    public function write(string $content): void
    {
        fwrite(STDOUT, $content);
    }
}
