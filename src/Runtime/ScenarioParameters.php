<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Runtime;

use Stateforge\Scenario\Core\Attribute\Parameter;
use Stateforge\Scenario\Core\Runtime\Exception\MissingRequiredParametersException;
use Stateforge\Scenario\Core\Runtime\Exception\NotAllowedParametersException;
use Stateforge\Scenario\Core\Runtime\Exception\ParameterValueErrorException;
use Stateforge\Scenario\Core\Runtime\Exception\UndefinedParameterException;
use function array_diff;
use function array_keys;
use function array_values;
use function count;
use function gettype;

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
        $this->validate();
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

    private function validate(): void
    {
        foreach ($this->allowedParameters as $parameter) {
            $value = $this->parameters[$parameter->name] ?? null;
            $value = $value === '' ? null : $value;
            if ($parameter->validate($value) === false) {
                throw new ParameterValueErrorException($parameter->name, $parameter->type->value, gettype($value), false);
            }
        }
    }

    public function get(string $name): mixed
    {
        if (isset($this->allowedParameters[$name]) === false) {
            throw new UndefinedParameterException($name);
        }

        $value = $this->allowedParameters[$name]->default;
        if (isset($this->parameters[$name]) === true
            && $this->parameters[$name] !== '') {
            $value = $this->parameters[$name];
        }

        return $this->allowedParameters[$name]->cast($value);
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        $parameters = [];
        foreach (array_keys($this->allowedParameters) as $name) {
            $parameters[$name] = $this->get($name);
        }

        return $parameters;
    }
}
