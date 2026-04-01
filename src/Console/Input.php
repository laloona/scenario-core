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

use Scenario\Core\Console\Exception\UndefinedArgumentException;
use Scenario\Core\Console\Exception\UndefinedOptionException;
use Scenario\Core\Console\Input\Argument;
use Scenario\Core\Console\Input\Option;
use Scenario\Core\Console\Input\Parser;
use Scenario\Core\Console\Input\Resolver;
use Scenario\Core\Contract\CliInput;
use function array_key_exists;

final class Input implements CliInput
{
    /**
     * @var array<string, null|int|float|bool|string>
     */
    private array $arguments = [];

    /**
     * @var array<string, null|int|float|bool|string|list<null|int|float|bool|string>>
     */
    private array $options = [];

    private Parser $parser;

    private Resolver $resolver;

    /**
     * @param list<string> $inputArgs
     */
    public function __construct(array $inputArgs)
    {
        $this->parser = new Parser($inputArgs);
        $this->resolver = new Resolver($this->parser);
    }

    public function command(): ?string
    {
        return $this->parser->command();
    }

    public function force(): bool
    {
        return $this->parser->force();
    }

    public function defineArgument(Argument $argument): void
    {
        $this->resolver->defineArgument($argument);
    }

    public function defineOption(Option $option): void
    {
        $this->resolver->defineOption($option);
    }

    public function resolve(): void
    {
        $this->arguments = $this->resolver->resolveArguments();
        $this->options = $this->resolver->resolveOptions();
    }

    public function argument(string $name): null|bool|string|int|float
    {
        if (array_key_exists($name, $this->arguments) === false) {
            throw new UndefinedArgumentException($name);
        }

        return $this->arguments[$name] ?? null;
    }

    /**
     * @return null|bool|string|int|float|list<null|int|float|bool|string>
     */
    public function option(string $name): null|bool|string|int|float|array
    {
        if (array_key_exists($name, $this->options) === false) {
            throw new UndefinedOptionException($name);
        }

        return $this->options[$name];
    }
}
