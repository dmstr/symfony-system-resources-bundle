<?php
// file generated with AI assistance: Claude Code - 2026-06-10 11:00:00 UTC

declare(strict_types=1);

namespace Dmstr\SymfonySystemResources\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use Dmstr\SymfonySystemResources\Dto\PruneResult;
use Dmstr\SymfonySystemResources\Security\SystemResourcesPermissions;
use Dmstr\SymfonySystemResources\State\MessengerMessagePruneProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Read-only ApiResource that maps the `messenger_messages` table managed
 * by the Symfony Messenger Doctrine transport. Provides an admin view of
 * the async message queue without exposing it as a writeable resource —
 * actual message dispatch goes through MessageBusInterface only.
 *
 * Schema is owned by Symfony Messenger; this class only declares the
 * columns ORM needs to hydrate them. Do not generate migrations from
 * this entity.
 */
#[ORM\Entity(readOnly: true)]
#[ORM\Table(name: self::TABLE)]
#[ApiResource(
    routePrefix: '/admin',
    operations: [
        new Get(),
        new GetCollection(),
        // Retention: deletes the messages of one queue older than `days`
        // (input schema MessengerMessage/prune.input.json); mirrored by
        // `app:messenger-messages:prune`
        new Post(
            uriTemplate: '/messenger_messages/prune',
            name: MessengerMessagePruneProcessor::OPERATION,
            description: 'Delete the messages of one queue older than a number of days',
            processor: MessengerMessagePruneProcessor::class,
            deserialize: false,
            validate: false,
            read: false,
            output: PruneResult::class,
            normalizationContext: ['groups' => [PruneResult::GROUP]],
            status: 200,
            security: "is_granted('" . SystemResourcesPermissions::MESSENGER_PRUNE . "')",
            // Marks the operation as custom action for consumers that
            // classify by this key (api-platform-utils-bundle 0.8+)
            extraProperties: ['custom_action' => true],
        ),
    ],
    security: "is_granted('" . SystemResourcesPermissions::MESSENGER_READ . "')",
    paginationItemsPerPage: 25,
    openapi: new Operation(tags: ['System'])
)]
#[ApiFilter(SearchFilter::class, properties: ['queueName' => 'exact'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt', 'availableAt', 'deliveredAt'])]
class MessengerMessage
{
    public const string TABLE = 'messenger_messages';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private ?string $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $body = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $headers = '';

    #[ORM\Column(name: 'queue_name', length: 190)]
    private string $queueName = '';

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'available_at', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $availableAt = null;

    #[ORM\Column(name: 'delivered_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $deliveredAt = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getHeaders(): string
    {
        return $this->headers;
    }

    public function getQueueName(): string
    {
        return $this->queueName;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getAvailableAt(): ?\DateTimeInterface
    {
        return $this->availableAt;
    }

    public function getDeliveredAt(): ?\DateTimeInterface
    {
        return $this->deliveredAt;
    }
}
// - revised 2026-10-28 (security expression checks the sysres:messenger_read permission)
// - revised 2026-11-27 (mark the prune operation as custom action)
