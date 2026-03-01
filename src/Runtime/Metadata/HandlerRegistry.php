<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Runtime\Metadata;

use Scenario\Core\Runtime\Exception\HandlerRegistryException;
use Scenario\Core\Runtime\Exception\RegistryException;
use Scenario\Core\Runtime\Metadata\Handler\AttributeHandler;
use Scenario\Core\Runtime\Registry;

final class HandlerRegistry extends Registry
{
    protected static ?HandlerRegistry $instance = null;

    public static function getInstance(): self
    {
        static::$instance = static::$instance ?? new static();
        return static::$instance;
    }

    /**
     * @var AttributeHandler[]
     */
    private array $registeredHandler = [];

    public function registerHandler(AttributeHandler $attributeHandler): void
    {
        if (isset($this->registeredHandler[$attributeHandler->supports(null)]) === true) {
            throw new HandlerRegistryException('Attribute ' . $attributeHandler->supports(null) . ' already registered.');
        }

        $this->registeredHandler[$attributeHandler->supports(null)] = $attributeHandler;
    }

    public function attributeHandler(string $attributeName): AttributeHandler
    {
        if (isset($this->registeredHandler[$attributeName]) === true) {
            return $this->registeredHandler[$attributeName];
        }

        throw new RegistryException('Attribute ' . $attributeName);
    }
}
