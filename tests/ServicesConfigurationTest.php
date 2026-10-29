<?php
// file generated with AI assistance: Claude Code - 2026-10-28 00:56:44 UTC

declare(strict_types=1);

namespace Dmstr\SymfonySystemResources\Tests;

use ApiPlatform\Metadata\ApiResource;
use Dmstr\SymfonySystemResources\Entity\DoctrineMigrationVersion;
use Dmstr\SymfonySystemResources\Entity\MessengerMessage;
use Dmstr\SymfonySystemResources\Security\SystemResourcesPermissions;
use Dmstr\SymfonySystemResources\SymfonySystemResourcesBundle;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Guards config/services.yaml, which the bundle imports in loadExtension().
 */
final class ServicesConfigurationTest extends TestCase
{
    private const string PERMISSION_TAG = 'za7_core.permission';

    /** Format of a permission string: `<bundle>:<action>`. */
    private const string PERMISSION_PATTERN = '/^[a-z][a-z0-9]*:[a-z][a-z0-9_]*$/';

    private const array RESOURCE_CLASSES = [
        MessengerMessage::class,
        DoctrineMigrationVersion::class,
    ];

    public function testServicesFileParses(): void
    {
        $container = $this->loadServices();

        self::assertInstanceOf(ContainerBuilder::class, $container);
    }

    public function testCarrierClassIsTheOnlyTaggedService(): void
    {
        $tagged = $this->loadServices()->findTaggedServiceIds(self::PERMISSION_TAG);

        self::assertSame([SystemResourcesPermissions::class], array_keys($tagged));
        self::assertCount(2, $tagged[SystemResourcesPermissions::class]);
    }

    public function testTaggedPermissionsAreTheBundleConstants(): void
    {
        self::assertSame(
            [
                SystemResourcesPermissions::MESSENGER_READ,
                SystemResourcesPermissions::MIGRATION_READ,
            ],
            $this->taggedPermissions(),
        );
    }

    public function testTaggedPermissionsMatchTheFormat(): void
    {
        self::assertSame([], self::formatViolations($this->loadServices()));
    }

    /**
     * The check that passes the real file must fail a deliberately broken,
     * in-memory definition.
     */
    #[DataProvider('malformedPermissions')]
    public function testFormatCheckRejectsMalformedPermission(string $permission): void
    {
        $container = new ContainerBuilder();
        $container->register('broken_permission_carrier')
            ->addTag(self::PERMISSION_TAG, ['permission' => $permission]);

        self::assertSame([$permission], self::formatViolations($container));
    }

    /** @return iterable<string, array{string}> */
    public static function malformedPermissions(): iterable
    {
        yield 'upper-case bundle' => ['Sysres:x'];
        yield 'empty action' => ['sysres:'];
        yield 'empty bundle' => [':messenger_read'];
        yield 'role name' => ['ROLE_ADMIN'];
        yield 'no separator' => ['sysres_messenger_read'];
        yield 'two separators' => ['sysres:messenger:read'];
        yield 'blank inside' => ['sysres: messenger_read'];
    }

    public function testEveryTaggedPermissionIsUsedByAResource(): void
    {
        $used = $this->resourcePermissions();

        foreach ($this->taggedPermissions() as $permission) {
            self::assertContains($permission, $used);
        }
    }

    public function testEveryResourcePermissionIsTagged(): void
    {
        $tagged = $this->taggedPermissions();
        $used = $this->resourcePermissions();

        self::assertNotEmpty($used);
        foreach ($used as $permission) {
            self::assertContains($permission, $tagged);
        }
    }

    public function testExtensionAlias(): void
    {
        $extension = (new SymfonySystemResourcesBundle())->getContainerExtension();

        self::assertNotNull($extension);
        self::assertSame('dmstr_symfony_system_resources', $extension->getAlias());
    }

    /**
     * Permission values of all `za7_core.permission` tags that do not match
     * the format; a missing `permission` attribute counts as an empty value.
     *
     * @return list<string>
     */
    private static function formatViolations(ContainerBuilder $container): array
    {
        return array_values(array_filter(
            self::permissionsOf($container),
            static fn (string $permission): bool => 1 !== preg_match(self::PERMISSION_PATTERN, $permission),
        ));
    }

    /** @return list<string> */
    private static function permissionsOf(ContainerBuilder $container): array
    {
        $permissions = [];
        foreach ($container->findTaggedServiceIds(self::PERMISSION_TAG) as $tags) {
            foreach ($tags as $attributes) {
                $permissions[] = (string) ($attributes['permission'] ?? '');
            }
        }

        return $permissions;
    }

    /** @return list<string> */
    private function taggedPermissions(): array
    {
        return self::permissionsOf($this->loadServices());
    }

    /**
     * Permissions referenced by `is_granted('…')` in the `security` strings of
     * the bundle's ApiResource attributes.
     *
     * @return list<string>
     */
    private function resourcePermissions(): array
    {
        $permissions = [];
        foreach (self::RESOURCE_CLASSES as $class) {
            $attributes = (new \ReflectionClass($class))->getAttributes(ApiResource::class);
            self::assertCount(1, $attributes, $class);

            $security = $attributes[0]->newInstance()->getSecurity() ?? '';
            preg_match_all("/is_granted\\('([^']*)'\\)/", $security, $matches);
            self::assertNotEmpty($matches[1], \sprintf('%s has no is_granted() check.', $class));

            array_push($permissions, ...$matches[1]);
        }

        return $permissions;
    }

    private function loadServices(): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../config'),
        );
        $loader->load('services.yaml');

        return $container;
    }
}
// - revised 2026-10-28 (assert the za7_core.permission tags against the resources)
