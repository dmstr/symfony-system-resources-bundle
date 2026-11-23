<?php
// file generated with AI assistance: Claude Code - 2026-11-23 23:02:48 UTC

declare(strict_types=1);

namespace Dmstr\SymfonySystemResources\Service;

use Dmstr\SymfonySystemResources\Dto\PruneResult;
use Dmstr\SymfonySystemResources\Entity\MessengerMessage;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

/**
 * Deletes the messages of one queue of the Doctrine Messenger transport that
 * were created more than a number of days ago.
 *
 * The Doctrine transport deletes a message once it is handled, so what
 * accumulates is mostly the failure transport's queue (every failed message
 * with its stack trace) — the queue a caller usually passes. The queue is
 * always explicit: pruning a working queue deletes work that was never done.
 *
 * Works on DBAL in batches of ids; the schema belongs to Symfony Messenger.
 */
class MessengerMessagePruner
{
    public const int DEFAULT_DAYS = 30;
    private const int BATCH_SIZE = 1000;

    public function __construct(
        private readonly Connection $connection,
        private readonly string $table = MessengerMessage::TABLE,
    ) {
    }

    public function prune(string $queueName, int $days = self::DEFAULT_DAYS, bool $dryRun = false): PruneResult
    {
        if ($queueName === '') {
            throw new \InvalidArgumentException('queueName must not be empty');
        }
        if ($days < 1) {
            throw new \InvalidArgumentException('days must be at least 1');
        }

        $before = new \DateTimeImmutable(sprintf('-%d days', $days));
        $where = 'queue_name = :queue AND created_at < :before';
        $params = ['queue' => $queueName, 'before' => $before];
        $types = ['before' => Types::DATETIME_IMMUTABLE];

        if ($dryRun) {
            $count = (int) $this->connection->fetchOne("SELECT COUNT(*) FROM {$this->table} WHERE $where", $params, $types);

            return new PruneResult($queueName, $days, $before, $count, true);
        }

        $deleted = 0;
        do {
            $ids = $this->connection->fetchFirstColumn(
                sprintf('SELECT id FROM %s WHERE %s ORDER BY id LIMIT %d', $this->table, $where, self::BATCH_SIZE),
                $params,
                $types
            );
            if ($ids !== []) {
                $deleted += (int) $this->connection->executeStatement(
                    "DELETE FROM {$this->table} WHERE id IN (:ids)",
                    ['ids' => $ids],
                    ['ids' => ArrayParameterType::INTEGER]
                );
            }
        } while (\count($ids) === self::BATCH_SIZE);

        return new PruneResult($queueName, $days, $before, $deleted, false);
    }
}
