<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Contract;

use Scenario\Core\Console\Output\Formatter\Align;

interface CliOutput
{
    public function confirm(string $question, bool $default = true): bool;

    public function headline(string $text): void;

    public function success(string $text): void;

    public function warn(string $text): void;

    public function error(string $text): void;

    /**
     * @param list<string>|null $headers
     * @param list<list<string|null>> $rows
     * @param list<Align>|null $align
     */
    public function table(?array $headers, array $rows, ?array $align = null, bool $showBorder = true): void;

    public function question(string $text): void;

    /**
     * @param list<string> $choices
     */
    public function choice(string $question, array $choices, ?string $default = null): string;

    public function ask(string $question, ?string $default = null, ?callable $validator = null): ?string;

    /**
     * @param string|list<string> $string
     */
    public function writeln(string|array $string): void;
}
