<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Runtime;

use Scenario\Core\Attribute\Parameter;
use Scenario\Core\Runtime\Exception\MissingRequiredParametersException;
use Scenario\Core\Runtime\Exception\NotAllowedParametersException;
use function array_diff;
use function array_key_exists;
use function array_keys;
use function array_values;

final class ScenarioParameters
{
    /**
     * @var array<string, Parameter>
     */
    private array $allowedParameters = [];

    /**
     * @param list<Parameter> $allowedParameters
     * @param array<string, mixed> $parameters
     */
    public function __construct(
        array $allowedParameters,
        private readonly array $parameters,
    ) {
        foreach ($allowedParameters as $parameter) {
            $this->allowedParameters[$parameter->name] = $parameter;
        }

        $this->resolve();
    }

    private function resolve(): void
    {
        $notAllowedParameters = array_values(array_diff(array_keys($this->parameters), array_keys($this->allowedParameters)));
        if (count($notAllowedParameters) > 0) {
            throw new NotAllowedParametersException($notAllowedParameters, array_keys($this->allowedParameters));
        }

        $requiredMissing = [];
        $missingParameters = array_values(array_diff(array_keys($this->allowedParameters), array_keys($this->parameters)));
        foreach ($missingParameters as $missingParameter) {
            if ($this->allowedParameters[$missingParameter]->required === true) {
                $requiredMissing[] = $missingParameter;
            }
        }

        if (count($requiredMissing) > 0) {
            throw new MissingRequiredParametersException($requiredMissing);
        }
    }

    public function get(string $name): mixed
    {
        if (array_key_exists($name, $this->parameters) === true) {
            return $this->parameters[$name];
        }

        return $this->allowedParameters[$name]->default;
    }
}
