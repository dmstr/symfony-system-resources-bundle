<!-- file generated with AI assistance: Claude Code - 2026-06-09 19:27:00 UTC -->

# dmstr/symfony-system-resources-bundle

Read-only API Platform admin resources for two framework-internal tables: `messenger_messages` (Symfony Messenger Doctrine transport) and `doctrine_migration_versions` (Doctrine Migrations). Operators can inspect queued and failed messages and the applied migrations through the API instead of opening a database shell.

## Installation

```bash
composer require dmstr/symfony-system-resources-bundle
```

Register the bundle in `config/bundles.php`:

```php
return [
    // ...
    Dmstr\SymfonySystemResources\SystemResourcesBundle::class => ['all' => true],
];
```

## Requirements

- PHP >= 8.5.
- Symfony SecurityBundle registered in the application. Without it API Platform ignores the `security` expression and both resources would be open to anyone, so the bundle requires it.
- API Platform 4 and Doctrine ORM.
- The tables already exist: `messenger_messages` from the Messenger Doctrine transport and `doctrine_migration_versions` from Doctrine Migrations. The bundle maps them read-only and manages no schema.

## Resources

Both resources offer item and collection reads only and appear under the OpenAPI tag `System`.

| Route | Entity | Permission |
|---|---|---|
| `/api/admin/messenger_messages` | `MessengerMessage` | `sysres:messenger_read` |
| `/api/admin/migration_versions` | `DoctrineMigrationVersion` | `sysres:migration_read` |

## Permissions

Access is checked against two permission attributes, not against a role:

- `sysres:messenger_read` for the Messenger messages
- `sysres:migration_read` for the migration versions

The constants are `Dmstr\SymfonySystemResources\Security\SystemResourcesPermissions::MESSENGER_READ` and `::MIGRATION_READ`. The bundle registers both attributes at the container tag `za7_core.permission`. The voter that decides them and the map from permission to roles come from `hrzg/za7-core`; this bundle has no dependency on za7-core, no configuration of its own and no default map. Which roles may read is therefore decided by the application:

```yaml
# config/packages/za7_core.yaml
za7_core:
    permissions:
        sysres:messenger_read: [<your-admin-role>]
        sysres:migration_read: [<your-admin-role>]
```

## Running without za7-core

Without za7-core you need your own security voter that supports `sysres:messenger_read` and `sysres:migration_read`. If no voter handles the attributes, every request is denied with 403, administrators included.

## Upgrading from 0.2.x

0.3.0 replaces the role check with the permission attributes, which is a breaking change. Deploy it together with the za7-core release that ships the permission voter, map both permissions under `za7_core.permissions`, and roll out both at the same time. Until the voter and the map are in place, every request to both resources gets 403.

The [CHANGELOG](./CHANGELOG.md) lists all changes per release.

## Tests

PHPUnit runs in a CLI-only container, no host PHP required:

```bash
docker compose run --rm php
```

## License

MIT © diemeisterei GmbH

<!-- - revised 2026-10-28 (0.3.0: permission attributes, rewrite) -->
