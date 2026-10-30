# Configuration
This document explains the shared Scenario configuration file used by Scenario Core and all framework adapters.

The configuration defines how Scenario discovers scenarios, parameter types, extensions, cache directories, and optional database integrations.

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
          cacheDirectory=".scenario.cache"
          parameterDirectory="scenario/parameter"
>
    <database>
        <connection>migration.php</connection>
    </database>

    <suites>
        <suite name="main" description="Main Scenario Suite">
            <directory>scenario/main</directory>
        </suite>
    </suites>

    <extensions>
        <bootstrap class="App\Scenario\Extension" />
    </extensions>
</scenario>
```

### Root Element

```xml
<scenario>
```
This is the root element of the configuration.

#### Attributes
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

### Extensions
The optional `<extensions>` element registers framework adapters and custom initialization logic.
Place it after `<suites>`:

```xml
<extensions>
    <bootstrap class="App\Scenario\Extension" />
</extensions>
```

Each `<bootstrap>` element requires a `class` attribute naming an autoloadable class
that implements `Stateforge\Scenario\Core\Extension`. Scenario instantiates the class
without constructor arguments. The element has no text content.

#### Creating a Custom Extension
Create the class referenced by the configuration and make it available through Composer autoloading.
For example, with an `App\` PSR-4 mapping to `src/`, save this as `src/Scenario/Extension.php`:

```php
<?php declare(strict_types=1);

namespace App\Scenario;

use Stateforge\Scenario\Core\Console\CliApplicationExtension;
use Stateforge\Scenario\Core\Extension as ExtensionInterface;
use Stateforge\Scenario\Core\Runtime\Application;
use Stateforge\Scenario\Core\Runtime\ApplicationExtension;

final class Extension implements ExtensionInterface
{
    public function application(): ?ApplicationExtension
    {
        return new class implements ApplicationExtension {
            public function prepare(Application $application): void
            {
                // Initialize dependencies needed to discover parameter types and scenarios.
            }

            public function boot(Application $application): void
            {
                // Initialize application services or register custom runtime handlers.
            }
        };
    }

    public function cli(): ?CliApplicationExtension
    {
        return null;
    }
}
```

`application()` returns a `Stateforge\Scenario\Core\Runtime\ApplicationExtension` or `null`.
Its `prepare(Application $application)` hook runs before parameter types and scenarios are loaded;
its `boot(Application $application)` hook runs during Scenario application bootstrap.
Return `null` when no application hooks are needed.

`cli()` returns a `Stateforge\Scenario\Core\Console\CliApplicationExtension` or `null`.
Its `boot(CliApplication $cli)` hook can register commands for `vendor/bin/scenario`
with `$cli->command('command-name', new CustomCommand())`, where `CustomCommand` extends
`Stateforge\Scenario\Core\Console\Command\CliCommand`.
Return `null` when no CLI customization is needed, as in the example above.

#### Combining Extensions and Framework Adapters
You can register multiple extensions by adding one `<bootstrap>` element per class.
Keep the framework adapter registered when adding your own extension:

```xml
<extensions>
    <bootstrap class="Stateforge\Scenario\Symfony\Extension" />
    <bootstrap class="App\Scenario\Extension" />
</extensions>
```

Use the adapter class provided by the installed package:

- Laravel: `Stateforge\Scenario\Laravel\Extension`
- Symfony: `Stateforge\Scenario\Symfony\Extension`

For standalone Scenario Core projects, register your own extensions without a framework adapter.
Framework console commands are registered through Laravel's or Symfony's own command integration;
the `cli()` hook customizes Scenario Core's CLI.

### Suites
Suites organize scenario classes into logical groups.

Example:
```xml
<suites>
    <suite name="main" description="Main Scenario Suite">
        <directory>scenario/main</directory>
    </suite>
    <suite name="cli" description="Cli for Tests">
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

When using Scenario Core standalone, register custom database setup behavior through an application extension.

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
- keep extension initialization logic minimal
- commit the configuration file to version control

---

## Next Steps
- [Scenarios](scenarios.md)
- [Parameter Types](parameter-types.md)
- [CLI Usage](cli.md)
- [Testing with PHPUnit](testing-with-phpunit.md)
- [Recipes](recipes.md)
