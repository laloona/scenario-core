# CLI Usage

This document describes the Scenario CLI and how to use it effectively.

---

## Overview

Scenario provides a CLI tool for applying and managing scenarios outside of tests.

Typical use cases:

- preparing local development data
- reproducing bugs
- setting up CI environments
- debugging specific states

---

## Basic Usage

```bash
php vendor/bin/scenario <command>
```

To see all available commands:
```bash
php vendor/bin/scenario
```

## Available Commands
- __install__: Adds the scenario extension to PHPUnit.
- __list__: List all available scenarios.<br>
  Available options (optional):
  ```bash
  --suite=<name>
  ```
- __apply__: Apply a scenario. Argument (optional): `scenario`<br>
  Available options (optional):
  ```bash
  --up       Apply the scenario (default)
  --down     Revert the scenario
  --audit    Print out the audit
   ```
  Parameters can be passed as CLI options: 
  ```php
  php vendor/bin/scenario apply create-user --parameter=email=test@example.com
  ```
  If parameters are not provided:
  - you may be prompted interactively
  - defaults will be used if defined
  If required parameters are missing, the CLI may ask:
  ```bash
  Please insert value for string parameter "email" (required)
  >
  ```
- __debug__: Inspect a scenario or test. Arguments (optional): `class` `method`<br>
  Use this to:
  - verify scenario resolution
  - inspect applied scenarios
  - debug execution flow
- __make__: Generate a new scenario. This command helps to create new scenarios quickly.
- __refresh__: Execute database or environment refresh logic.<br>
  Available options (optional):
  ```bash
  --connection=<name>
  ```
  > **Note:** Scenario Core does not implement database logic.
  This depends on your project configuration.

---

## Next Steps

- [Testing with PHPUnit](testing-with-phpunit.md)
- [Recipes](recipes.md)
