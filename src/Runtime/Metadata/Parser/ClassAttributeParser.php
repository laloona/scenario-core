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
use Scenario\Core\Runtime\Application;

final class ClassAttributeParser
{
    /**
     * @param class-string $className
     * @return array<ReflectionAttribute<object>>
     * @throws ReflectionException
     */
    public function parse(string $className): array
    {
        $attributes = [];

        $config = Application::config();
        if ($config !== null) {
            foreach ($config->getAttributes() as $attribute) {
                $attributes = array_merge(
                    $attributes,
                    (new ReflectionClass($className))->getAttributes($attribute),
                );
            }
        }

        return $attributes;
    }
}
