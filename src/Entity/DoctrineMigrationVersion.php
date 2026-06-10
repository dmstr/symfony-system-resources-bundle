<?php
// file generated with AI assistance: Claude Code - 2026-06-10 11:00:00 UTC

declare(strict_types=1);

namespace Dmstr\SymfonySystemResources\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Read-only ApiResource that maps the `doctrine_migration_versions`
 * table managed by the Doctrine Migrations Bundle. Provides an admin
 * view of the migration log without exposing it as a writeable resource —
 * actual migration execution goes through `doctrine:migrations:migrate`.
 *
 * Schema is owned by Doctrine Migrations; this class only declares the
 * columns ORM needs to hydrate them. Do not generate migrations from
 * this entity.
 */
#[ORM\Entity(readOnly: true)]
#[ORM\Table(name: 'doctrine_migration_versions')]
#[ApiResource(
    routePrefix: '/admin',
    shortName: 'MigrationVersion',
    operations: [
        new Get(),
        new GetCollection(),
    ],
    security: "is_granted('ROLE_ADMIN')",
)]
class DoctrineMigrationVersion
{
    #[ORM\Id]
    #[ORM\Column(length: 191)]
    private string $version = '';

    #[ORM\Column(name: 'executed_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $executedAt = null;

    #[ORM\Column(name: 'execution_time', type: Types::INTEGER, nullable: true)]
    private ?int $executionTime = null;

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getExecutedAt(): ?\DateTimeInterface
    {
        return $this->executedAt;
    }

    public function getExecutionTime(): ?int
    {
        return $this->executionTime;
    }
}
