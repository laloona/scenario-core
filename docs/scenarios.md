# Scenarios

This document explains how scenarios are defined, composed, and used in Scenario Core.

It focuses on structure, behavior, and best practices.

---

## What is a Scenario?

A scenario describes a **reproducible application state**.

It is a PHP class that:

- implements `ScenarioInterface`
- is marked with `#[AsScenario]`
- defines how a state is created (`up`) and optionally removed (`down`)

## Basic Structure

```php
<?php declare(strict_types=1);

use Stateforge\Scenario\Core\Attribute\AsScenario;
use Stateforge\Scenario\Core\Contract\ScenarioInterface;

#[AsScenario('create-user')]
final class CreateUserScenario implements ScenarioInterface
{
    public function up(): void
    {
        // create data
    }

    public function down(): void
    {
        // optional cleanup
    }
}
```

## Scenario Naming

The name defined in `#[AsScenario]` is used:
- in CLI (scenario apply create-user)
- in `#[ApplyScenario]`
- as a unique identifier

### Best practices
- use descriptive names: user-with-subscription
- keep names stable (avoid breaking references)
- prefer kebab-case or snake_case

## Applying Scenarios

Scenarios are applied using the `#[ApplyScenario]` attribute.

```php
use Stateforge\Scenario\Core\Attribute\ApplyScenario;

#[ApplyScenario('create-user')]
final class MyTest extends TestCase
{
}
```

You can also reference the class directly:
```php
#[ApplyScenario(CreateUserScenario::class)]
```

## Scenario Composition

Scenarios can apply other scenarios.
```php
#[ApplyScenario(UserExists::class)]
#[ApplyScenario(UserHasSubscription::class)]
final class UserReadyScenario implements ScenarioInterface
{
    public function up(): void {}
}
```

### Why composition matters
- reuse smaller building blocks
- avoid duplication
- keep scenarios focused

### Execution Order

Scenarios are executed in the order they are applied.
- class-level attributes run before method-level attributes
- composed scenarios run before the current scenario

## Parameters
Scenarios can define parameters using `#[Parameter]`.

```php
use Stateforge\Scenario\Core\Attribute\Parameter;
use Stateforge\Scenario\Core\Runtime\Metadata\ParameterType;

#[AsScenario('create-user')]
#[Parameter('email', ParameterType::String, required: true)]
final class CreateUserScenario implements ScenarioInterface
{
    public function up(string $email): void
    {
        // use parameter
    }
}
```

### Passing parameters

#### CLI:
```php
php vendor/bin/scenario apply create-user --email=test@example.com
```

#### PHPUnit:
```php
#[ApplyScenario(CreateUserScenario::class, ['email' => 'test@example.com'])]
```

### Parameter Behavior
- parameters are validated before execution
- invalid values throw exceptions
- optional parameters may define defaults

## Up and Down Methods

### `up()`

Defines how the state is created:
- should be deterministic
- should not depend on external state

### `down()`

Optional cleanup method.

Use when:
- state needs to be reverted
- scenarios are used in reversible workflows

## Idempotency

Scenarios should ideally be idempotent:
- running them multiple times should not break the system
- avoid duplicate data creation
- check existing state when needed

## Error Handling

Scenario Core handles:
- invalid parameters
- missing scenarios
- execution failures

Failures are surfaced via exceptions and PHPUnit integration.

## Best Practices

- Keep scenarios small: Avoid large, monolithic scenarios.
    Prefer:
    - UserExists
    - UserHasSubscription
    instead of:
    - FullUserSetupScenario
- Prefer composition: Build complex states from smaller scenarios.
- Avoid hidden dependencies : Do not rely on implicit state.
- Always make dependencies explicit via:
   - composition
   - parameters
- Use clear naming: Names should describe the resulting state, not the implementation.
- Keep logic minimal: Scenarios should orchestrate state, not contain business logic.

---

## Next Steps

- [CLI Usage](cli.md)
- [Testing with PHPUnit](testing-with-phpunit.md)
- [Recipes](recipes.md)
