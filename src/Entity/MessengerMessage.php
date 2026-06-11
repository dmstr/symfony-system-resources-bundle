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
use ApiPlatform\OpenApi\Model\Operation;
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
#[ORM\Table(name: 'messenger_messages')]
#[ApiResource(
    routePrefix: '/admin',
    operations: [
        new Get(),
        new GetCollection(),
    ],
    security: "is_granted('ROLE_ADMIN')",
    paginationItemsPerPage: 25,
    openapi: new Operation(tags: ['ZA7 Core'])
)]
#[ApiFilter(SearchFilter::class, properties: ['queueName' => 'exact'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt', 'availableAt', 'deliveredAt'])]
class MessengerMessage
{
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
