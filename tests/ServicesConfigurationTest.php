<?php
// file generated with AI assistance: Claude Code - 2026-10-28 00:56:44

declare(strict_types=1);

namespace Dmstr\SymfonySystemResources\Tests;

use Dmstr\SymfonySystemResources\SystemResourcesBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Guards config/services.yaml, which the bundle imports in loadExtension().
 */
final class ServicesConfigurationTest extends TestCase
{
    public function testServicesFileParses(): void
    {
        $container = $this->loadServices();

        self::assertInstanceOf(ContainerBuilder::class, $container);
    }

    public function testNoPermissionIsRegisteredYet(): void
    {
        $container = $this->loadServices();

        self::assertSame([], $container->findTaggedServiceIds('za7_core.permission'));
    }

    public function testExtensionAlias(): void
    {
        $extension = (new SystemResourcesBundle())->getContainerExtension();

        self::assertNotNull($extension);
        self::assertSame('dmstr_symfony_system_resources', $extension->getAlias());
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
