<!-- file generated with AI assistance: Claude Code - 2026-10-28 00:56:44 UTC -->

# Changelog

All notable changes to this bundle. The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/); versions follow [SemVer](https://semver.org/) and come from the Git tags.

## [0.3.0] - 2026-10-29

### Changed

- **BREAKING**: Both resources require a permission attribute instead of a role: `/api/admin/messenger_messages` requires `sysres:messenger_read`, `/api/admin/migration_versions` requires `sysres:migration_read`. 0.2.x required the Symfony role `ROLE_ADMIN`. Without a voter that handles the attributes and a map that grants them, every request now gets 403, administrators included. Routes, filters, pagination and the OpenAPI tag `System` are unchanged.
- **BREAKING**: PHP >= 8.5 is required.

### Added

- Both permissions are registered at the container tag `za7_core.permission` through the carrier class `Dmstr\SymfonySystemResources\Security\SystemResourcesPermissions`; the voter and the permission-to-role map come from `hrzg/za7-core`.
- Bundle service configuration in `config/services.yaml`.
- New dependencies `symfony/security-bundle` (without a registered SecurityBundle API Platform ignores `security` and the resources would be open) and `symfony/yaml`.

### Migration

Use the za7-core release that ships the permission voter, map `sysres:messenger_read` and `sysres:migration_read` to your admin role under `za7_core.permissions`, and roll out both together. Applications without za7-core need their own voter for the two attributes.

## [0.2.0] - 2026-06-14

### Changed

- OpenAPI tag `System` replaces `ZA7 Core`.
- PHP >= 8.4 is required.

### Added

- PHPUnit tests with a CLI-only Docker Compose runner and a GitHub Action.
- `CLAUDE.md` with the repository's language policy.

## [0.1.1] - 2026-06-11

### Added

- OpenAPI tag `ZA7 Core` on both resources.

## [0.1.0] - 2026-06-10

### Added

- Initial release: `MessengerMessage` and `DoctrineMigrationVersion` as read-only API Platform resources under `/admin`.

[0.3.0]: https://github.com/dmstr/symfony-system-resources-bundle/compare/0.2.0...0.3.0
[0.2.0]: https://github.com/dmstr/symfony-system-resources-bundle/compare/0.1.1...0.2.0
[0.1.1]: https://github.com/dmstr/symfony-system-resources-bundle/compare/0.1.0...0.1.1
[0.1.0]: https://github.com/dmstr/symfony-system-resources-bundle/releases/tag/0.1.0
