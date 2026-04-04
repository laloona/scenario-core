<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Contract;

use Stateforge\Scenario\Core\Console\Exception\InputException;
use Stateforge\Scenario\Core\Console\Input\Argument;
use Stateforge\Scenario\Core\Console\Input\Option;

interface CliInput
{
    public function command(): ?string;

    public function argument(string $name): null|bool|string|float|int;

    /**
     * @return null|bool|string|float|int|list<null|bool|string|float|int>
     */
    public function option(string $name): null|bool|string|float|int|array;

    public function defineArgument(Argument $argument): void;

    public function defineOption(Option $option): void;

    /**
     * @throws InputException
     */
    public function resolve(): void;
}
