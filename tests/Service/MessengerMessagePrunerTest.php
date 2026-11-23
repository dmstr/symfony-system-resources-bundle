<?php
// file generated with AI assistance: Claude Code - 2026-11-23 23:03:27 UTC

declare(strict_types=1);

namespace Dmstr\SymfonySystemResources\Tests\Service;

use Dmstr\OpenApiJsonSchema\Service\InputSchemaValidator;
use Dmstr\OpenApiJsonSchema\Service\OperationInputSchemaResolver;
use Dmstr\SymfonySystemResources\Command\PruneMessengerMessagesCommand;
use Dmstr\SymfonySystemResources\Service\MessengerMessagePruner;
use Dmstr\SymfonySystemResources\State\MessengerMessagePruneProcessor;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use ApiPlatform\Metadata\Post;

/**
 * Pruner, REST processor and CLI mirror on an in-memory SQLite table shaped
 * like the Doctrine Messenger transport's.
 */
final class MessengerMessagePrunerTest extends TestCase
{
    private Connection $connection;
    private MessengerMessagePruner $pruner;
    private InputSchemaValidator $validator;

    protected function setUp(): void
    {
        $this->connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $this->connection->executeStatement('CREATE TABLE messenger_messages (
            id INTEGER PRIMARY KEY AUTOINCREMENT, body TEXT NOT NULL, headers TEXT NOT NULL,
            queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL,
            available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL)');
        // [queue, age in days]
        foreach ([['failed', 40], ['failed', 35], ['failed', 2], ['default', 40]] as [$queue, $age]) {
            $at = (new \DateTimeImmutable("-$age days"))->format('Y-m-d H:i:s');
            $this->connection->insert('messenger_messages', [
                'body' => '{}', 'headers' => '{}', 'queue_name' => $queue, 'created_at' => $at, 'available_at' => $at,
            ]);
        }
        $this->pruner = new MessengerMessagePruner($this->connection);
        $this->validator = new InputSchemaValidator(new OperationInputSchemaResolver(
            [],
            [[\dirname(__DIR__, 2).'/src/Entity', 'Dmstr\\SymfonySystemResources\\Entity']]
        ));
    }

    public function testDeletesOldMessagesOfTheGivenQueueOnly(): void
    {
        $result = $this->pruner->prune('failed', 30);

        self::assertSame(2, $result->deleted);
        self::assertSame(['default', 'failed'], $this->remainingQueues());
    }

    public function testDryRunOnlyCounts(): void
    {
        $result = $this->pruner->prune('failed', 30, true);

        self::assertSame(2, $result->deleted);
        self::assertTrue($result->dryRun);
        self::assertCount(4, $this->remainingQueues());
    }

    public function testProcessorValidatesTheBody(): void
    {
        $result = $this->processor('{"queueName": "failed", "days": 30}')
            ->process(null, new Post());
        self::assertSame(2, $result->deleted);

        $this->expectException(UnprocessableEntityHttpException::class);
        $this->processor('{"days": 30}')->process(null, new Post());
    }

    public function testInvalidDaysAreRejected(): void
    {
        $this->expectException(UnprocessableEntityHttpException::class);
        $this->processor('{"queueName": "failed", "days": 0}')->process(null, new Post());
    }

    public function testCommandMirrorsTheOperation(): void
    {
        $tester = new CommandTester(new PruneMessengerMessagesCommand($this->validator, $this->pruner));

        self::assertSame(0, $tester->execute(['--queue' => 'failed', '--dry-run' => true]));
        self::assertStringContainsString('Would delete 2', $tester->getDisplay());
        self::assertCount(4, $this->remainingQueues());

        self::assertSame(0, $tester->execute(['--queue' => 'failed', '--days' => '30']));
        self::assertSame(['default', 'failed'], $this->remainingQueues());

        // Without a queue nothing is deleted
        self::assertSame(2, $tester->execute(['--days' => '30']));
        self::assertSame(2, $tester->execute(['--queue' => 'failed', '--days' => '0']));
    }

    private function processor(string $body): MessengerMessagePruneProcessor
    {
        $requests = new RequestStack();
        $requests->push(new Request(content: $body));

        return new MessengerMessagePruneProcessor($this->validator, $this->pruner, $requests);
    }

    /**
     * @return list<string>
     */
    private function remainingQueues(): array
    {
        return $this->connection->fetchFirstColumn('SELECT queue_name FROM messenger_messages ORDER BY queue_name');
    }
}
