<!-- file generated with AI assistance: Claude Code - 2026-06-09 19:27:00 UTC -->

# dmstr/symfony-system-resources-bundle

Read-only API Platform admin resources for Symfony framework-internal tables.

## Features (planned)

- `MessengerMessage` entity mapping `messenger_messages` (Symfony Messenger
  Doctrine transport) — exposed as `#[ApiResource(routePrefix: '/admin',
  operations: [Get, GetCollection])]` → `/api/admin/messenger_messages`
- `DoctrineMigrationVersion` entity mapping `doctrine_migration_versions`
  (Doctrine Migrations Bundle) — exposed as `#[ApiResource(routePrefix:
  '/admin', operations: [Get, GetCollection])]` → `/api/admin/migration_versions`
- Both protected with `security: "is_granted('ROLE_ADMIN')"`
- No schema management — entities map existing tables managed by the
  respective framework components

## License

MIT © diemeisterei GmbH
