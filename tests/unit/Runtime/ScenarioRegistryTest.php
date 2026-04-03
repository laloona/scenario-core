<?php declare(strict_types=1);

/*
 * This file is part of Scenario\Core package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scenario\Core\Tests\Unit\Runtime;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Scenario\Core\Attribute\AsScenario;
use Scenario\Core\Contract\ScenarioInterface;
use Scenario\Core\Runtime\Exception\DefinitionClassAlreadyRegisteredException;
use Scenario\Core\Runtime\Exception\DefinitionException;
use Scenario\Core\Runtime\Exception\DefinitionNameAlreadyRegisteredException;
use Scenario\Core\Runtime\Exception\InvalidScenarioSubClassException;
use Scenario\Core\Runtime\Exception\RegistryException;
use Scenario\Core\Runtime\ScenarioDefinition;
use Scenario\Core\Runtime\ScenarioRegistry;
use Scenario\Core\Tests\Files\AnotherScenario;
use Scenario\Core\Tests\Files\InvalidScenario;
use Scenario\Core\Tests\Files\ValidScenario;
use Scenario\Core\Tests\Unit\ScenarioRegistryMock;

#[CoversClass(ScenarioRegistry::class)]
#[UsesClass(AsScenario::class)]
#[UsesClass(DefinitionClassAlreadyRegisteredException::class)]
#[UsesClass(DefinitionNameAlreadyRegisteredException::class)]
#[UsesClass(InvalidScenarioSubClassException::class)]
#[UsesClass(RegistryException::class)]
#[UsesClass(ScenarioDefinition::class)]
#[Group('runtime')]
#[Small]
final class ScenarioRegistryTest extends TestCase
{
    use ScenarioRegistryMock;

    protected function tearDown(): void
    {
        $this->resetScenarioRegistry();
    }

    public function testGetInstanceReturnsSameInstance(): void
    {
        $firstInstance = ScenarioRegistry::getInstance();
        $secondInstance = ScenarioRegistry::getInstance();

        self::assertSame($firstInstance, $secondInstance);
    }

    public function testRegisterStoresDefinitionByClass(): void
    {
        $registry = ScenarioRegistry::getInstance();

        $definition = new ScenarioDefinition(
            'main',
            ValidScenario::class,
            new AsScenario(null, null),
            [],
        );

        $registry->register($definition);

        self::assertSame($definition, $registry->resolve(ValidScenario::class));
    }

    public function testRegisterAlsoStoresDefinitionByNameWhenProvided(): void
    {
        $registry = ScenarioRegistry::getInstance();

        $definition = new ScenarioDefinition(
            'main',
            ValidScenario::class,
            new AsScenario('my-scenario', null),
            [],
        );

        $registry->register($definition);

        self::assertSame($definition, $registry->resolve(ValidScenario::class));
        self::assertSame($definition, $registry->resolve('my-scenario'));
    }

    public function testRegisterDoesNotStoreAliasWhenNameIsNull(): void
    {
        $registry = ScenarioRegistry::getInstance();

        $definition = new ScenarioDefinition(
            'main',
            ValidScenario::class,
            new AsScenario(null, null),
            [],
        );

        $registry->register($definition);

        self::assertSame($definition, $registry->resolve(ValidScenario::class));
        self::assertSame([ValidScenario::class => $definition], $registry->all());

        $this->expectException(RegistryException::class);
        $registry->resolve('');
    }

    public function testRegisterDoesNotStoreEmptyNameAlias(): void
    {
        $registry = ScenarioRegistry::getInstance();

        $definition = new ScenarioDefinition(
            'main',
            ValidScenario::class,
            new AsScenario('', null),
            [],
        );

        $registry->register($definition);

        self::assertSame($definition, $registry->resolve(ValidScenario::class));

        $this->expectException(RegistryException::class);
        $this->expectExceptionMessage('scenario  is not registered');

        $registry->resolve('');
    }

    public function testAllReturnsEmptyArrayWhenNothingRegistered(): void
    {
        self::assertSame([], ScenarioRegistry::getInstance()->all());
    }

    public function testAllReturnsRegisteredScenariosIncludingAliasByName(): void
    {
        $registry = ScenarioRegistry::getInstance();

        $definition = new ScenarioDefinition(
            'main',
            ValidScenario::class,
            new AsScenario('my-scenario', null),
            [],
        );

        $registry->register($definition);

        $all = $registry->all();

        self::assertCount(2, $all);
        self::assertArrayHasKey(ValidScenario::class, $all);
        self::assertArrayHasKey('my-scenario', $all);
        self::assertSame($definition, $all[ValidScenario::class]);
        self::assertSame($definition, $all['my-scenario']);
    }

    public function testClearRemovesAllRegisteredScenarios(): void
    {
        $registry = ScenarioRegistry::getInstance();

        $definition = new ScenarioDefinition(
            'main',
            ValidScenario::class,
            new AsScenario('my-scenario', null),
            [],
        );

        $registry->register($definition);

        self::assertCount(2, $registry->all());

        $registry->clear();

        self::assertSame([], $registry->all());

        $this->expectException(RegistryException::class);
        $registry->resolve(ValidScenario::class);
    }

    public function testResolveThrowsRegistryExceptionIfNotFound(): void
    {
        $this->expectException(RegistryException::class);
        $this->expectExceptionMessage('scenario does-not-exist is not registered');

        ScenarioRegistry::getInstance()->resolve('does-not-exist');
    }

    public function testRegisterThrowsDefinitionExceptionIfClassIsNotScenarioInterface(): void
    {
        $registry = ScenarioRegistry::getInstance();

        $definition = new ScenarioDefinition(
            'main',
            InvalidScenario::class,
            new AsScenario('my-scenario', null),
            [],
        );

        $this->expectException(DefinitionException::class);
        $this->expectExceptionMessage(InvalidScenario::class . ' is not a subclass of ' . ScenarioInterface::class);

        $registry->register($definition);
    }

    public function testRegisterThrowsWhenAliasIsAlreadyRegisteredForAnotherScenario(): void
    {
        $registry = ScenarioRegistry::getInstance();

        $firstDefinition = new ScenarioDefinition(
            'main',
            ValidScenario::class,
            new AsScenario('alias', null),
            [],
        );
        $registry->register($firstDefinition);

        $secondDefinition = new ScenarioDefinition(
            'main',
            AnotherScenario::class,
            new AsScenario('alias', null),
            [],
        );

        $this->expectException(DefinitionException::class);
        $this->expectExceptionMessage('scenario name alias already registered for ' . ValidScenario::class);

        $registry->register($secondDefinition);
    }

    public function testRegisterThrowsWhenSameClassIsAlreadyRegistered(): void
    {
        $registry = ScenarioRegistry::getInstance();

        $firstDefinition = new ScenarioDefinition(
            'main',
            ValidScenario::class,
            new AsScenario('first', null),
            [],
        );

        $secondDefinition = new ScenarioDefinition(
            'main',
            ValidScenario::class,
            new AsScenario('second', null),
            [],
        );

        $registry->register($firstDefinition);

        $this->expectException(DefinitionException::class);
        $this->expectExceptionMessage(ValidScenario::class . ' is already registered');

        $registry->register($secondDefinition);

        self::assertSame($secondDefinition, $registry->resolve(ValidScenario::class));
        self::assertSame($firstDefinition, $registry->resolve('first'));
    }

    public function testConstructMethodIsPrivate(): void
    {
        $method = new ReflectionMethod(ScenarioRegistry::class, '__construct');

        self::assertTrue($method->isPrivate());
    }

    public function testCloneMethodIsPrivate(): void
    {
        $method = new ReflectionMethod(ScenarioRegistry::class, '__clone');

        self::assertTrue($method->isPrivate());
    }
}
