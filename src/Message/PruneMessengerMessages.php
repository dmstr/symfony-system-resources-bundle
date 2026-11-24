<?php
// file generated with AI assistance: Claude Code - 2026-11-23 23:02:48 UTC

declare(strict_types=1);

namespace Dmstr\SymfonySystemResources\Message;

use Dmstr\SymfonySystemResources\Service\MessengerMessagePruner;

/**
 * Asks for a prune run of one queue, e.g. as a recurring message of the
 * application's scheduler:
 * `RecurringMessage::every('1 day', new PruneMessengerMessages('failed'))`.
 */
final class PruneMessengerMessages
{
    public function __construct(
        public readonly string $queueName,
        public readonly int $days = MessengerMessagePruner::DEFAULT_DAYS,
    ) {
    }
}
