<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Console;

use Scenario\Core\Contract\CliInput;
use function array_shift;
use function array_values;
use function explode;
use function str_starts_with;
use function substr;

final class Input implements CliInput
{
    private ?string $command;

    /**
     * @var list<bool|string>
     */
    private array $arguments;

    /**
     * @var array<string, bool|string>
     */
    private array $options = [];

    /**
     * @param list<string> $inputArgs
     */
    public function __construct(array $inputArgs)
    {
        array_shift($inputArgs);
        $this->parseOptions($inputArgs);
        $this->arguments = array_values($inputArgs);
        $this->command = array_shift($this->arguments);
    }

    /**
     * @param list<string> $inputArgs
     * @param-out array<int, string> $inputArgs
     */
    private function parseOptions(array &$inputArgs): void
    {
        foreach ($inputArgs as $key => $arg) {
            if (str_starts_with($arg, '--')) {
                unset($inputArgs[$key]);
                $option = explode('=', substr($arg, 2));
                $this->options[$option[0]] = $option[1] ?? true;
            }
        }
    }

    public function command(): ?string
    {
        return $this->command;
    }

    public function argument(string $name): null|bool|string
    {
        return $this->arguments[(int)$name] ?? null;
    }

    public function option(string $name): null|bool|string
    {
        return $this->options[$name] ?? null;
    }
}
