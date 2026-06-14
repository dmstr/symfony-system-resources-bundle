<?php
// file generated with AI assistance: Claude Code - 2026-06-13 23:14:54 UTC

declare(strict_types=1);

namespace Dmstr\SymfonySystemResources\Tests\Entity;

use Dmstr\SymfonySystemResources\Entity\DoctrineMigrationVersion;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the read-only {@see DoctrineMigrationVersion} entity.
 *
 * Hydrated by Doctrine only (no setters); getter mapping is verified by
 * populating the private properties via reflection.
 */
final class DoctrineMigrationVersionTest extends TestCase
{
    public function testDefaults(): void
    {
        $version = new DoctrineMigrationVersion();

        self::assertSame('', $version->getVersion());
        self::assertNull($version->getExecutedAt());
        self::assertNull($version->getExecutionTime());
    }

    public function testGettersReflectHydratedValues(): void
    {
        $executedAt = new \DateTime('2026-06-10 11:00:00');
        $version = $this->hydrate([
            'version' => 'Herzog\\Migrations\\Version20260610110000',
            'executedAt' => $executedAt,
            'executionTime' => 1234,
        ]);

        self::assertSame('Herzog\\Migrations\\Version20260610110000', $version->getVersion());
        self::assertSame($executedAt, $version->getExecutedAt());
        self::assertSame(1234, $version->getExecutionTime());
    }

    /** @param array<string,mixed> $values */
    private function hydrate(array $values): DoctrineMigrationVersion
    {
        $entity = new DoctrineMigrationVersion();
        $reflection = new \ReflectionObject($entity);
        foreach ($values as $property => $value) {
            $reflection->getProperty($property)->setValue($entity, $value);
        }

        return $entity;
    }
}
