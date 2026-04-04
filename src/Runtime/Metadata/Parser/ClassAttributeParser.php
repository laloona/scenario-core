<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Runtime\Metadata\Parser;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use Stateforge\Scenario\Core\Runtime\Application;
use function in_array;

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
            $foundAttributes = (new ReflectionClass($className))->getAttributes();
            foreach ($foundAttributes as $attribute) {
                if (in_array($attribute->getName(), $config->getAttributes(), true)) {
                    $attributes[] = $attribute;
                }
            }
        }

        return $attributes;
    }
}
