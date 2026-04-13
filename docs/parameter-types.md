# Parameter Types
This document explains how parameter types validate, convert, and register input values in Scenario Core.

---

## What is a Parameter Type?

A parameter type validates and converts input values before a scenario is executed.

Parameter types ensure that scenarios receive clean and predictable values.

---

## Built-in Core Types
Scenario Core provides the following built-in parameter types:
- `String`
- `Integer`
- `Float`
- `Boolean`

Example:
```php
#[Parameter('age', ParameterType::Integer)]
```

## Using Custom Parameter Types
You may use your own classes as parameter types.

A custom parameter type must:
- extend ParameterTypeDefinition
- use `#[AsParameterType]`

Example:
```php
use Stateforge\Scenario\Core\ParameterTypeDefinition;

#[AsParameterType('Validates email addresses.')]
final class EmailType extends ParameterTypeDefinition
{
    public function cast(mixed $value): string|int|float|bool|null
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL)
            ? (string) $value
            : null;
    }
}
```

Usage:
```php
#[Parameter('email', EmailType::class)]
```

## Automatic Discovery
Custom parameter types are automatically loaded from the configured parameter directories.

Example XML:
```xml
<scenario parameter-directory="scenario/Parameter" />
```

## Descriptions
You may provide a description using:

```php
#[AsParameterType('Validates email addresses.')]
```
This description is shown in CLI listings.

## Conditional Parameter Types
Some parameter types should only be available when specific runtime requirements are met.

Examples:
- a required PHP extension is installed
- a package is available
- an environment-specific dependency exists
- a feature flag is enabled

Scenario Core supports this through conditional parameter type registration.
```php
#[ParameterTypeCondition(SomeCondition::class)]
```

If the condition returns false, the parameter type is skipped during registration.

Example:
```php
#[AsParameterType('Validates special values.')]
#[ParameterTypeCondition(MyCondition::class)]
final class SpecialType extends ParameterTypeDefinition
{
}
```

### Condition Class
A condition class determines whether the parameter type should be registered.

Example:
```php
use Stateforge\Scenario\Core\ParameterTypeCondition;

final class MyCondition extends ParameterTypeCondition
{
    public function matches(): bool
    {
        return extension_loaded('intl');
    }
}
```

### Typical Use Cases
Use conditions when parameter types depend on optional runtime features.

Examples:
- ext-intl
- ext-gd
- database drivers
- framework-specific components
- optional third-party libraries

### Why This Matters
Conditional loading allows packages to provide optional parameter types without forcing additional dependencies.

This keeps installations lightweight and avoids unnecessary runtime errors.

### Best Practices
- Keep conditions small and deterministic
- Use conditions only for optional dependencies
- Add a clear description to the parameter type when requirements exist
- Avoid business logic inside condition classes

## Listing Available Types

Use the CLI command to display all built-in and registered parameter types:
```bash
php vendor/bin/scenario parameter
```
This is useful to verify that:
- your custom parameter type was discovered correctly
- conditional parameter types were enabled
- optional dependencies are available
- framework adapter types were registered
- the expected runtime configuration is active

It is especially helpful during development when adding new parameter types.

---

## Next Steps
- [CLI Usage](cli.md)
- [Testing with PHPUnit](testing-with-phpunit.md)
- [Recipes](recipes.md)
