<?php
// file generated with AI assistance: Claude Code - 2026-06-13 23:14:54 UTC

declare(strict_types=1);

namespace Dmstr\SymfonySystemResources\Tests\Entity;

use Dmstr\SymfonySystemResources\Entity\MessengerMessage;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the read-only {@see MessengerMessage} entity.
 *
 * The entity has no setters (it is hydrated by Doctrine only), so getter
 * mapping is verified by populating the private properties via reflection —
 * exactly as the ORM does — and asserting each getter returns the right field.
 */
final class MessengerMessageTest extends TestCase
{
    public function testDefaults(): void
    {
        $message = new MessengerMessage();

        self::assertNull($message->getId());
        self::assertSame('', $message->getBody());
        self::assertSame('', $message->getHeaders());
        self::assertSame('', $message->getQueueName());
        self::assertNull($message->getCreatedAt());
        self::assertNull($message->getAvailableAt());
        self::assertNull($message->getDeliveredAt());
    }

    public function testGettersReflectHydratedValues(): void
    {
        $created = new \DateTime('2026-01-01 09:00:00');
        $available = new \DateTime('2026-01-01 09:00:05');
        $delivered = new \DateTime('2026-01-01 09:00:10');

        $message = $this->hydrate([
            'id' => '42',
            'body' => '{"type":"FooMessage"}',
            'headers' => '{"Content-Type":"application/json"}',
            'queueName' => 'default',
            'createdAt' => $created,
            'availableAt' => $available,
            'deliveredAt' => $delivered,
        ]);

        self::assertSame('42', $message->getId());
        self::assertSame('{"type":"FooMessage"}', $message->getBody());
        self::assertSame('{"Content-Type":"application/json"}', $message->getHeaders());
        self::assertSame('default', $message->getQueueName());
        self::assertSame($created, $message->getCreatedAt());
        self::assertSame($available, $message->getAvailableAt());
        self::assertSame($delivered, $message->getDeliveredAt());
    }

    /** @param array<string,mixed> $values */
    private function hydrate(array $values): MessengerMessage
    {
        $message = new MessengerMessage();
        $reflection = new \ReflectionObject($message);
        foreach ($values as $property => $value) {
            $reflection->getProperty($property)->setValue($message, $value);
        }

        return $message;
    }
}
