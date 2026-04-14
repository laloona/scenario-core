# Configuration
This document explains the shared Scenario configuration file used by Scenario Core and all framework adapters.

The configuration defines how Scenario discovers scenarios, parameter types, bootstrap files, cache directories, and optional database integrations.

---

## Overview
Scenario uses an XML configuration file.

This file is framework-independent and shared across:
- Scenario Core
- Scenario Symfony adapter
- Scenario Laravel adapter
- future adapters

The same configuration model is used everywhere.

---

## Creating the Configuration
The configuration file can be generated using the CLI command:
```bash
php vendor/bin/scenario make
```
You can select the `config` option to generate the XML file in the project root directory.

## Default File Location
The default configuration file name is:
```text
scenario.dist.xml
```
located in the project root directory.

Projects may copy this file to `scenario.xml` for local customization.

## Example Configuration
```xml
<scenario xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
          xsi:noNamespaceSchemaLocation="vendor/stateforge/scenario-core/xsd/scenario.xsd"
          bootstrap="scenario/bootstrap.php"
          cacheDirectory=".scenario.cache"
          parameterDirectory="scenario/parameter"
>
    <database>
        <connection>migration.php</connection>
    </database>

    <suites>
        <suite name="Main Scenario Suite">
            <directory>scenario/main</directory>
        </suite>
    </suites>
</scenario>
```

### Root Element

```xml
<scenario>
```
This is the root element of the configuration.

#### Attributes
##### bootstrap
```xml
bootstrap="scenario/bootstrap.php"
```
The optional file is loaded automatically during application bootstrap.

Use this to register adapters or custom initialization logic.

Example use cases:
- register framework adapters
- custom runtime setup
- service bootstrapping

##### cacheDirectory
```xml
cacheDirectory=".scenario.cache"
```
Directory used for generated cache files.

Scenario caches discovered scenarios and parameter types for faster startup.

##### parameterDirectory
```xml
parameterDirectory="scenario/parameter"
```
Directory containing custom parameter type classes.

All compatible parameter types in this directory are discovered automatically.

### Suites
Suites organize scenario classes into logical groups.

Example:
```xml
<suites>
    <suite name="Main Scenario Suite">
        <directory>scenario/main</directory>
    </suite>
    <suite name="Cli for Tests">
        <directory>scenario/cli</directory>
    </suite>
</suites>
```

#### Suite Attributes
##### name
Human-readable suite name.
Used in CLI output and interactive commands.

#### directory
Directory containing scenario classes.

All scenarios inside this directory are discovered automatically.

### Multiple Suites
You may define multiple suites.

This is useful for separating:
- test scenarios
- development scenarios
- fixtures
- domain-specific states

### Database Configuration
Optional database connections may be configured.
```xml
<database>
    <connection name="default">migration.php</connection>
</database>
```
Scenario Core does not assume a specific ORM, migration tool, or framework.

Instead, database setup is delegated to the configured PHP migration script.

This means the project decides how a database is prepared, for example:
- running migrations
- recreating the schema
- importing fixtures
- truncating tables
- resetting test databases

When using Scenario Core standalone, you are responsible for implementing database setup logic yourself inside the configured bootstrap file.

This keeps Scenario Core framework-independent.

## Schema Validation
The configuration file is validated against the bundled XML schema:
```xml
vendor/stateforge/scenario-core/xsd/scenario.xsd
```
Invalid configuration values will fail early during bootstrap.

## Best Practices
- keep the file in project root
- use descriptive suite names
- separate scenarios into multiple suites when needed
- keep bootstrap logic minimal.
- commit the configuration file to version control

---

## Next Steps
- [Scenarios](scenarios.md)
- [Parameter Types](parameter-types.md)
- [CLI Usage](cli.md)
- [Testing with PHPUnit](testing-with-phpunit.md)
- [Recipes](recipes.md)
