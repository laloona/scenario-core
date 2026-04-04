<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Runtime\Metadata;

use Stateforge\Scenario\Core\Runtime\Exception\HandlerRegistryException;
use Stateforge\Scenario\Core\Runtime\Exception\RegistryException;
use Stateforge\Scenario\Core\Runtime\Metadata\Handler\AttributeHandler;

final class HandlerRegistry
{
    private static ?HandlerRegistry $instance = null;

    public static function getInstance(): self
    {
        static::$instance = static::$instance ?? new static();
        return static::$instance;
    }

    private function __construct()
    {
    }

    private function __clone()
    {
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
