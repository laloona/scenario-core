# Changelog

## [1.1.0] - 2026-04-xx

### Added
- Introduced extensible parameter type system for validating and converting scenario input values.
- Added support for custom parameter types via `#[AsParameterType]`.
- Added automatic discovery of parameter types from configured parameter directories.
- Added optional conditional parameter type loading via `#[ParameterTypeCondition]`.
- Added `parameter` CLI command to list built-in and registered parameter types.
- Added support for generating custom parameter types via the `make` command.

### Changed
- Moved `ParameterType` from `Stateforge\Scenario\Core\Metadata\Parameter\ParameterType`
  to `Stateforge\Scenario\Core\ParameterType`.

## [1.0.2] - 2026-04-18

### Changed
- Updated PHPUnit version constraints to exclude releases affected by published security advisories.
- Raised minimum supported PHPUnit patch versions for safer dependency resolution.

### Security
- Prevented installation of PHPUnit versions flagged by Packagist security advisories.
- Improved Composer compatibility with secure PHPUnit release lines.

## [1.0.1] - 2026-04-12

### Added
- added logo and badges and minor corrections in docs

## [1.0.0] - 2026-04-06

### Added
- Initial release of the Scenario Core framework.
- Scenario-based system state orchestration.
- CLI support and blueprint-based generation.

### Stability
- Cross-platform support (Linux, Windows).
- Normalized path handling.

### Notes
- First stable release.
- API considered production-ready.
