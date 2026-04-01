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

use function function_exists;
use function getenv;
use function is_string;
use function posix_isatty;
use function shell_exec;
use function stream_isatty;
use const PHP_OS_FAMILY;
use const STDOUT;

final class SystemTerminal implements TerminalEnvironment
{
    public function noColorEnv(): bool
    {
        return getenv('NO_COLOR') !== false;
    }

    public function isTty(): ?bool
    {
        if (function_exists('stream_isatty') === true) {
            return @stream_isatty(STDOUT);
        }

        if (function_exists('posix_isatty') === true) {
            return @posix_isatty(STDOUT);
        }

        return null;
    }

    public function columnsEnv(): ?string
    {
        $cols = getenv('COLUMNS');
        return is_string($cols) === true
            ? $cols
            : null;
    }

    public function shellExec(string $command): ?string
    {
        if (function_exists('shell_exec') === false) {
            return null;
        }

        $out = shell_exec($command);
        return is_string($out) === true
            ? $out
            : null;
    }

    public function osFamily(): string
    {
        return PHP_OS_FAMILY;
    }
}
