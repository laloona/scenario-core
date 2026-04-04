<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Console\Input;

use function array_shift;
use function array_values;
use function explode;
use function is_array;
use function str_starts_with;
use function substr;

final class Parser
{
    private bool $force = false;

    private ?string $command;

    /**
     * @var list<string>
     */
    private array $arguments;

    /**
     * @var array<string, bool|string|array<int, bool|string>>
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
                $option = explode('=', substr($arg, 2), 2);
                $name = $option[0];
                $value = $option[1] ?? true;
                if (isset($this->options[$name]) === true) {
                    if (is_array($this->options[$name]) === false) {
                        $this->options[$name] = [$this->options[$name]];
                    }
                    $this->options[$name][] = $value;
                } else {
                    $this->options[$name] = $value;
                }
            }
        }

        if (isset($this->options['force']) === true) {
            $this->force = true;
            unset($this->options['force']);
        }
    }

    public function command(): ?string
    {
        return $this->command;
    }

    public function force(): bool
    {
        return $this->force;
    }

    /**
     * @return list<string>
     */
    public function arguments(): array
    {
        return $this->arguments;
    }

    /**
     * @return array<string, bool|string|array<int, bool|string>>
     */
    public function options(): array
    {
        return $this->options;
    }
}
