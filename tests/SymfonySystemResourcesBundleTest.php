<?php
// file generated with AI assistance: Claude Code - 2026-10-29 18:05:00 UTC

declare(strict_types=1);

namespace Dmstr\SymfonySystemResources\Tests;

use Dmstr\SymfonySystemResources\SymfonySystemResourcesBundle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SymfonySystemResourcesBundle::class)]
final class SymfonySystemResourcesBundleTest extends TestCase
{
    /**
     * Symfony Flex registers a bundle without recipe only under the class name
     * it derives from the PSR-4 namespace: last segment plus "Bundle". Any
     * other name and a new app never gets the bundle in bundles.php.
     */
    public function testClassNameMatchesTheFlexAutoRecipeGuess(): void
    {
        $composer = json_decode((string) file_get_contents(__DIR__.'/../composer.json'), true, flags: \JSON_THROW_ON_ERROR);
        $namespace = rtrim((string) array_search('src/', $composer['autoload']['psr-4'], true), '\\');
        $segments = explode('\\', $namespace);

        self::assertSame($namespace.'\\'.end($segments).'Bundle', SymfonySystemResourcesBundle::class);
    }
}
