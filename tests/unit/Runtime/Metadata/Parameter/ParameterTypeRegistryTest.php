<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\Core\Tests\Unit\Runtime\Metadata\Parameter;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionProperty;
use Stateforge\Scenario\Core\Attribute\AsParameterType;
use Stateforge\Scenario\Core\Attribute\ParameterTypeCondition;
use Stateforge\Scenario\Core\ParameterTypeCondition as BaseParameterTypeCondition;
use Stateforge\Scenario\Core\ParameterTypeDefinition;
use Stateforge\Scenario\Core\Runtime\Exception\Metadata\InvalidParameterTypeException;
use Stateforge\Scenario\Core\Runtime\Exception\Metadata\ParameterTypeAlreadyRegisteredException;
use Stateforge\Scenario\Core\Runtime\Exception\Metadata\UnknownParameterTypeException;
use Stateforge\Scenario\Core\Runtime\Metadata\Parameter\ParameterTypeRegistry;
use Stateforge\Scenario\Core\Runtime\Metadata\ValueType\IntegerType;
use Stateforge\Scenario\Core\Runtime\Metadata\ValueType\StringType;
use Stateforge\Scenario\Core\Tests\Files\ConditionallyDisabledParameterType;
use Stateforge\Scenario\Core\Tests\Files\IntegerParameterType;
use Stateforge\Scenario\Core\Tests\Files\InvalidParameterType;
use function array_map;

#[CoversClass(ParameterTypeRegistry::class)]
#[UsesClass(AsParameterType::class)]
#[UsesClass(ParameterTypeCondition::class)]
#[UsesClass(BaseParameterTypeCondition::class)]
#[UsesClass(IntegerType::class)]
#[UsesClass(StringType::class)]
#[UsesClass(InvalidParameterTypeException::class)]
#[UsesClass(ParameterTypeAlreadyRegisteredException::class)]
#[UsesClass(ParameterTypeDefinition::class)]
#[UsesClass(UnknownParameterTypeException::class)]
#[Group('runtime')]
#[Small]
final class ParameterTypeRegistryTest extends TestCase
{
    protected function tearDown(): void
    {
        $property = new ReflectionProperty(ParameterTypeRegistry::class, 'instance');
        $property->setValue(null, null);
    }

    public function testGetInstanceReturnsSameInstance(): void
    {
        $firstInstance = ParameterTypeRegistry::getInstance();
        $secondInstance = ParameterTypeRegistry::getInstance();

        self::assertSame($firstInstance, $secondInstance);
    }

    public function testRegisterAndResolveReturnNewParameterTypeInstance(): void
    {
        $registry = ParameterTypeRegistry::getInstance();
        $registry->register(IntegerParameterType::class, new AsParameterType('integer'));

        $resolved = $registry->resolve(IntegerParameterType::class);

        self::assertInstanceOf(IntegerParameterType::class, $resolved);
        self::assertSame(IntegerParameterType::class, $resolved->value);
        self::assertSame('integer', $registry->all()[IntegerParameterType::class]->description);
    }

    public function testAllReturnsRegisteredParameterTypes(): void
    {
        $registry = ParameterTypeRegistry::getInstance();
        $registry->register(IntegerParameterType::class, new AsParameterType('integer'));

        self::assertSame(
            [IntegerParameterType::class => 'integer'],
            array_map(
                static fn (AsParameterType $asParameterType): ?string => $asParameterType->description,
                $registry->all(),
            ),
        );
    }

    public function testRegisterThrowsForInvalidParameterTypeClass(): void
    {
        $this->expectException(InvalidParameterTypeException::class);
        $this->expectExceptionMessage('given ' . InvalidParameterType::class . ' is not a valid parameter type');

        ParameterTypeRegistry::getInstance()->register(InvalidParameterType::class, new AsParameterType());
    }

    public function testRegisterThrowsWhenParameterTypeIsAlreadyRegistered(): void
    {
        $registry = ParameterTypeRegistry::getInstance();
        $registry->register(IntegerParameterType::class, new AsParameterType('integer'));

        $this->expectException(ParameterTypeAlreadyRegisteredException::class);
        $this->expectExceptionMessage('parameter type "' . IntegerParameterType::class . '" is already registered');

        $registry->register(IntegerParameterType::class, new AsParameterType('integer-updated'));
    }

    public function testResolveThrowsWhenParameterTypeIsUnknown(): void
    {
        $this->expectException(UnknownParameterTypeException::class);
        $this->expectExceptionMessage('parameter type ' . IntegerParameterType::class . ' is not registered');

        ParameterTypeRegistry::getInstance()->resolve(IntegerParameterType::class);
    }

    public function testRegisterSkipsParameterTypeWhenConditionDoesNotMatch(): void
    {
        $registry = ParameterTypeRegistry::getInstance();
        $registry->register(ConditionallyDisabledParameterType::class, new AsParameterType());

        $this->expectException(UnknownParameterTypeException::class);
        $this->expectExceptionMessage(
            'parameter type ' . ConditionallyDisabledParameterType::class . ' is not registered',
        );

        $registry->resolve(ConditionallyDisabledParameterType::class);
    }

    public function testConstructMethodIsPrivate(): void
    {
        self::assertTrue((new ReflectionMethod(ParameterTypeRegistry::class, '__construct'))->isPrivate());
    }

    public function testCloneMethodIsPrivate(): void
    {
        self::assertTrue((new ReflectionMethod(ParameterTypeRegistry::class, '__clone'))->isPrivate());
    }
}
