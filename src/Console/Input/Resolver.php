<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Console\Input;

use Scenario\Core\Console\Exception\MissingRequiredArgumentsException;
use Scenario\Core\Console\Exception\MissingRequiredOptionsException;
use Scenario\Core\Console\Exception\NotAllowedArgumentsException;
use Scenario\Core\Console\Exception\NotAllowedOptionsException;
use function array_key_exists;
use function array_keys;
use function array_values;
use function count;
use function is_array;

final class Resolver
{
    /**
     * @var list<Argument>
     */
    private array $definedArguments = [];

    /**
     * @var list<Option>
     */
    private array $definedOptions = [];

    public function __construct(private readonly Parser $parser)
    {
    }

    public function defineArgument(Argument $argument): void
    {
        $this->definedArguments[] = $argument;
    }

    public function defineOption(Option $option): void
    {
        $this->definedOptions[] = $option;
    }

    /**
     * @return array<string, null|int|float|bool|string>
     */
    public function resolveArguments(): array
    {
        /** @var array<string, null|int|float|bool|string> $resolvedArguments */
        $resolvedArguments = [];
        $missing = [];

        $arguments = $this->parser->arguments();
        foreach ($this->definedArguments as $i => $definedArgument) {
            if (array_key_exists($i, $arguments) === false
                && $definedArgument->required === true) {
                $missing[] = $definedArgument->name;
                continue;
            }

            $resolvedArguments[$definedArgument->name] = $definedArgument->cast($arguments[$i] ?? null);
            unset($arguments[$i]);
        }

        if (count($missing) > 0) {
            throw new MissingRequiredArgumentsException($missing);
        }

        if (count($arguments) > 0) {
            throw new NotAllowedArgumentsException(array_values($arguments), array_keys($resolvedArguments));
        }

        return $resolvedArguments;
    }

    /**
     * @return array<string, null|int|float|bool|string|list<null|int|float|bool|string>>
     */
    public function resolveOptions(): array
    {
        /** @var array<string, null|int|float|bool|string|list<null|int|float|bool|string>> $resolvedOptions */
        $resolvedOptions = [];
        $missing = [];

        $options = $this->parser->options();
        foreach ($this->definedOptions as $definedOption) {
            if (array_key_exists($definedOption->name, $options) === false
                && $definedOption->required === true) {
                $missing[] = $definedOption->name;
                continue;
            }

            if ($definedOption->repeatable === true
                && isset($options[$definedOption->name]) === true
                && is_array($options[$definedOption->name]) === false) {
                $options[$definedOption->name] = [ $options[$definedOption->name] ];
            }

            $resolvedOptions[$definedOption->name] = $definedOption->cast($options[$definedOption->name] ?? null);
            unset($options[$definedOption->name]);
        }

        if (count($missing) > 0) {
            throw new MissingRequiredOptionsException($missing);
        }

        if (count($options) > 0) {
            throw new NotAllowedOptionsException(array_keys($options), array_keys($resolvedOptions));
        }

        return $resolvedOptions;
    }
}
