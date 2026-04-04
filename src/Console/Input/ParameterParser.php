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

use function explode;
use function is_array;
use function is_string;

final class ParameterParser
{
    /**
     * @param null|bool|string|float|int|list<null|bool|string|float|int> $input
     * @return array<string, string|null|list<string|null>>
     */
    public function parse(null|bool|string|float|int|array $input): array
    {
        $parameters = [];

        if (is_array($input) === true) {
            foreach ($input as $parameter) {
                if (is_string($parameter) === true) {
                    $parameters = $this->extract($parameters, $parameter);
                }
            }
        }

        if (is_string($input) === true) {
            $parameters = $this->extract($parameters, $input);
        }

        return $parameters;
    }

    /**
     * @param array<string, string|null|list<string|null>> $parameters
     * @return array<string, string|null|list<string|null>>
     */
    private function extract(array $parameters, string $input): array
    {
        $value = explode('=', $input, 2);
        $name = $value[0];
        $value = $value[1] ?? null;

        if (isset($parameters[$name])) {
            $parameters[$name] = is_array($parameters[$name]) === true
                ? [...$parameters[$name], $value]
                : [$parameters[$name], $value];
        } else {
            $parameters[$name] = $value;
        }

        return $parameters;
    }
}
