<?php
// file generated with AI assistance: Claude Code - 2026-10-28 07:08:40 UTC

declare(strict_types=1);

namespace Dmstr\SymfonySystemResources\Security;

/**
 * Permission strings of this bundle, checked by the ApiResource `security`
 * expressions of the system resources.
 *
 * This class is only the carrier of the `za7_core.permission` container tags
 * declared in config/services.yaml. The voter that decides these permissions
 * and the permission-to-role map live in hrzg/za7-core; this bundle does not
 * depend on za7-core. Without such a voter no voter supports these
 * attributes, so they are denied for everyone. The class is never
 * instantiated.
 */
final class SystemResourcesPermissions
{
    public const string MESSENGER_READ = 'sysres:messenger_read';
    public const string MIGRATION_READ = 'sysres:migration_read';

    private function __construct()
    {
    }
}
