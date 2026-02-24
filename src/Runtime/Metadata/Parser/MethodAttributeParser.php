<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Runtime\Metadata\Parser;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;

final class MethodAttributeParser
{
    /**
     * @param class-string $className
     * @return array<ReflectionAttribute<object>>
     * @throws ReflectionException
     */
    public function parse(string $className, string $methodName): array
    {
        return new ReflectionClass($className)->getMethod($methodName)->getAttributes();
    }
}
