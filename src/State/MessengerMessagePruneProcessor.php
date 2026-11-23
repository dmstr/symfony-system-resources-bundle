<?php
// file generated with AI assistance: Claude Code - 2026-11-23 23:02:48 UTC

declare(strict_types=1);

namespace Dmstr\SymfonySystemResources\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Dmstr\OpenApiJsonSchema\Service\InputSchemaValidator;
use Dmstr\SymfonySystemResources\Dto\PruneResult;
use Dmstr\SymfonySystemResources\Service\MessengerMessagePruner;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * POST /admin/messenger_messages/prune: validates the body against
 * `Entity/MessengerMessage/prune.input.json` and deletes the messages of
 * `queueName` older than `days`.
 *
 * @implements ProcessorInterface<mixed, PruneResult>
 */
final class MessengerMessagePruneProcessor implements ProcessorInterface
{
    public const string OPERATION = 'messenger_message_prune';

    public function __construct(
        private readonly InputSchemaValidator $validator,
        private readonly MessengerMessagePruner $pruner,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): PruneResult
    {
        $input = $this->validator->validateRaw(
            self::OPERATION,
            $this->requestStack->getCurrentRequest()?->getContent() ?? ''
        );

        return $this->pruner->prune(
            (string) $input['queueName'],
            (int) ($input['days'] ?? MessengerMessagePruner::DEFAULT_DAYS),
            (bool) ($input['dryRun'] ?? false)
        );
    }
}
