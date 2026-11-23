<?php
// file generated with AI assistance: Claude Code - 2026-11-23 23:02:48 UTC

declare(strict_types=1);

namespace Dmstr\SymfonySystemResources\MessageHandler;

use Dmstr\SymfonySystemResources\Message\PruneMessengerMessages;
use Dmstr\SymfonySystemResources\Service\MessengerMessagePruner;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class PruneMessengerMessagesHandler
{
    public function __construct(
        private readonly MessengerMessagePruner $pruner,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(PruneMessengerMessages $message): void
    {
        $result = $this->pruner->prune($message->queueName, $message->days);
        $this->logger->info('Pruned Messenger messages', [
            'queue' => $result->queueName,
            'deleted' => $result->deleted,
            'before' => $result->before->format(\DateTimeInterface::ATOM),
        ]);
    }
}
