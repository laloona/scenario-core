# Recipes

This document contains practical examples of how to use Scenario in real-world situations.

---

## Create a User for Tests

### Problem

Many tests require a user to exist.

### Solution

```php
#[AsScenario('user-exists')]
#[Parameter('id', ParameterType::Integer, required: true)]
final class UserExists implements ScenarioInterface
{
    public function up(int $id): void
    {
        // create user with given id
    }
}
```
Use in tests:
```php
#[ApplyScenario(UserExists::class, ['id' => 42])]
```

## User with Subscription

### Problem

You need a user with an active subscription.

### Solution

Compose scenarios:
```php
#[AsScenario('user-with-subscription')]
#[ApplyScenario(UserExists::class)]
final class UserHasSubscription implements ScenarioInterface
{
    public function up(): void
    {
        // create subscription for existing user
    }
}
```

## Combine Multiple States

### Problem

Tests require a fully prepared system state.

### Solution

```php
#[ApplyScenario(UserExists::class, ['id' => 42])]
#[ApplyScenario(UserHasSubscription::class)]
final class SubscriptionTest extends TestCase
{
}
```

## Reset Database Before Test

### Problem

Tests interfere with each other.

### Solution

```php
#[RefreshDatabase]
final class MyTest extends TestCase
{
}
```

## Reproduce a Bug Locally

### Problem

A bug only occurs with specific data.

### Solution
1. Create a scenario:
```php
#[AsScenario('bug-123-state')]
final class Bug123State implements ScenarioInterface
{
    public function up(): void
    {
        // prepare exact failing state
    }
}
```
2. Apply it:
```bash
php vendor/bin/scenario apply bug-123-state
```
Now you can debug the issue in a reproducible environment.

