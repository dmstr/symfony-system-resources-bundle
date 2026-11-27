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
    Dmstr\SymfonySystemResources\SymfonySystemResourcesBundle::class => ['all' => true],
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

### Pruning Messenger messages

The Doctrine transport deletes a message once it is handled. What accumulates in `messenger_messages` is mostly the failure transport's queue: every failed message with its stack trace. Three entry points delete the messages of **one** queue created more than `days` days ago (default 30):

| Entry point | Input |
|---|---|
| `POST /api/admin/messenger_messages/prune` (operation `messenger_message_prune`, permission `sysres:messenger_prune`) | `{"queueName": "failed", "days": 30, "dryRun": false}`, schema `src/Entity/MessengerMessage/prune.input.json`, published as the OpenAPI request body |
| `bin/console app:messenger-messages:prune --queue=failed --days=30 [--dry-run]` | validated against the same schema |
| message `Dmstr\SymfonySystemResources\Message\PruneMessengerMessages('failed', days: 30)` | for the application's scheduler |

`POST /api/admin/messenger_messages/prune` carries `extraProperties: ['custom_action' => true]`, so consumers that classify custom actions by this marker (api-platform-utils-bundle 0.8 and later) keep listing it as an action in the Hydra documentation.

`queueName` is required and has no default: pruning a working queue deletes work that was never done. If a working queue grows, the workers do not keep up; that is not solved by pruning.

On MySQL the first run on a large table writes every deleted row to the binary log; check the free disk space and start with a dry run. `OPTIMIZE TABLE messenger_messages` returns the freed space to the file system afterwards.

## Permissions

Access is checked against three permission attributes, not against a role:

- `sysres:messenger_read` for the Messenger messages
- `sysres:migration_read` for the migration versions
- `sysres:messenger_prune` for deleting old messages of a queue

The constants are `Dmstr\SymfonySystemResources\Security\SystemResourcesPermissions::MESSENGER_READ`, `::MIGRATION_READ` and `::MESSENGER_PRUNE`. The bundle registers the attributes at the container tag `za7_core.permission`. The voter that decides them and the map from permission to roles come from `hrzg/za7-core`; this bundle has no dependency on za7-core, no configuration of its own and no default map. Which roles may read is therefore decided by the application:

```yaml
# config/packages/za7_core.yaml
za7_core:
    permissions:
        sysres:messenger_read: [<your-admin-role>]
        sysres:migration_read: [<your-admin-role>]
        sysres:messenger_prune: [<your-admin-role>]
```

## Running without za7-core

Without za7-core you need your own security voter that supports `sysres:messenger_read`, `sysres:migration_read` and `sysres:messenger_prune`. If no voter handles the attributes, every request is denied with 403, administrators included.

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
<!-- - revised 2026-11-27 (prune operation named as marked custom action) -->
