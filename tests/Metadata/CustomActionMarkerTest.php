<?php
// file generated with AI assistance: Claude Code - 2026-11-27 00:24:06 UTC

declare(strict_types=1);

namespace Dmstr\SymfonySystemResources\Tests\Metadata;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\HttpOperation;
use Dmstr\SymfonySystemResources\Entity\DoctrineMigrationVersion;
use Dmstr\SymfonySystemResources\Entity\MessengerMessage;
use Dmstr\SymfonySystemResources\State\MessengerMessagePruneProcessor;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Pins the `custom_action` marker in the attribute metadata of the
 * bundle's ApiResources: exactly the prune operation carries
 * `extraProperties['custom_action'] === true`, nothing else does.
 *
 * Reads the `#[ApiResource]` attribute instances directly, without
 * kernel and metadata factory. The attribute instance does not merge the
 * resource's `extraProperties` into its operations (only the factory
 * does), so the resource level is asserted separately.
 */
final class CustomActionMarkerTest extends TestCase
{
    private const string MARKER = 'custom_action';

    private const array CLASSES = [
        MessengerMessage::class,
        DoctrineMigrationVersion::class,
    ];

    /** @return iterable<string, array{class-string}> */
    public static function resources(): iterable
    {
        yield 'messenger' => [MessengerMessage::class];
        yield 'migration' => [DoctrineMigrationVersion::class];
    }

    public function testThePruneOperationCarriesTheMarker(): void
    {
        $found = [];
        foreach (self::CLASSES as $class) {
            foreach (self::operations($class) as $operation) {
                if ($operation->getName() === MessengerMessagePruneProcessor::OPERATION) {
                    $found[] = [$class, $operation];
                }
            }
        }

        self::assertCount(1, $found);
        [$class, $operation] = $found[0];
        self::assertSame(MessengerMessage::class, $class);
        self::assertInstanceOf(HttpOperation::class, $operation);
        self::assertSame('/messenger_messages/prune', $operation->getUriTemplate());
        self::assertSame('POST', $operation->getMethod());
        self::assertSame(true, $operation->getExtraProperties()[self::MARKER] ?? null);
    }

    /** @param class-string $class */
    #[DataProvider('resources')]
    public function testNoOtherOperationCarriesTheMarker(string $class): void
    {
        $operations = self::operations($class);

        // Not vacuous: each resource has the item and collection read operations at least
        self::assertGreaterThan(1, \count($operations));
        foreach ($operations as $name => $operation) {
            if ($operation->getName() === MessengerMessagePruneProcessor::OPERATION) {
                continue;
            }
            self::assertArrayNotHasKey(
                self::MARKER,
                $operation->getExtraProperties(),
                \sprintf('%s operation %s', $class, $name),
            );
        }
    }

    /** @param class-string $class */
    #[DataProvider('resources')]
    public function testTheResourceDoesNotCarryTheMarker(string $class): void
    {
        self::assertArrayNotHasKey(
            self::MARKER,
            self::resource($class)->getExtraProperties(),
            \sprintf('%s resource', $class),
        );
    }

    /**
     * @param class-string $class
     *
     * @return array<string, \ApiPlatform\Metadata\Operation>
     */
    private static function operations(string $class): array
    {
        $operations = self::resource($class)->getOperations();
        self::assertNotNull($operations);

        return iterator_to_array($operations);
    }

    /** @param class-string $class */
    private static function resource(string $class): ApiResource
    {
        return (new \ReflectionClass($class))
            ->getAttributes(ApiResource::class)[0]
            ->newInstance();
    }
}
