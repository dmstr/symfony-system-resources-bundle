<?php
// file generated with AI assistance: Claude Code - 2026-10-28 07:08:40 UTC

declare(strict_types=1);

namespace Dmstr\SymfonySystemResources\Tests\Security;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Symfony\Security\ResourceAccessChecker;
use Dmstr\SymfonySystemResources\Entity\DoctrineMigrationVersion;
use Dmstr\SymfonySystemResources\Entity\MessengerMessage;
use Dmstr\SymfonySystemResources\Security\SystemResourcesPermissions;
use Dmstr\SymfonySystemResources\Tests\Fixtures\MapPermissionVoter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolver;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Authorization\ExpressionLanguage;
use Symfony\Component\Security\Core\Authorization\Strategy\AffirmativeStrategy;
use Symfony\Component\Security\Core\Role\RoleHierarchy;
use Symfony\Component\Security\Core\User\InMemoryUser;

/**
 * Evaluates the real `security` strings of the bundle's ApiResources with
 * API Platform's real ResourceAccessChecker, without kernel and database.
 *
 * The za7-core voter is replaced by the dependency-free fixture
 * {@see MapPermissionVoter}; all role names are invented for this test.
 */
final class ResourceSecurityTest extends TestCase
{
    private const string ROLE_OPS = 'ROLE_SYSRES_TEST_OPS';
    private const string ROLE_DBA = 'ROLE_SYSRES_TEST_DBA';
    private const string ROLE_OTHER = 'ROLE_SYSRES_TEST_OTHER';
    private const string ROLE_LEAD = 'ROLE_SYSRES_TEST_LEAD';

    private const array HIERARCHY = [
        self::ROLE_LEAD => [self::ROLE_OPS],
    ];

    private const array MAP = [
        SystemResourcesPermissions::MESSENGER_READ => [self::ROLE_OPS],
        SystemResourcesPermissions::MIGRATION_READ => [self::ROLE_DBA],
    ];

    /** @return iterable<string, array{class-string, string}> */
    public static function resources(): iterable
    {
        yield 'messenger' => [
            MessengerMessage::class,
            SystemResourcesPermissions::MESSENGER_READ,
        ];
        yield 'migration' => [
            DoctrineMigrationVersion::class,
            SystemResourcesPermissions::MIGRATION_READ,
        ];
    }

    /** @return iterable<string, array{class-string, string, string}> */
    public static function resourcesWithRoles(): iterable
    {
        yield 'messenger' => [
            MessengerMessage::class,
            self::ROLE_OPS,
            self::ROLE_DBA,
        ];
        yield 'migration' => [
            DoctrineMigrationVersion::class,
            self::ROLE_DBA,
            self::ROLE_OPS,
        ];
    }

    /** @param class-string $class */
    #[DataProvider('resources')]
    public function testResourceSecurityChecksThePermission(string $class, string $permission): void
    {
        $resource = self::resource($class);

        self::assertSame("is_granted('" . $permission . "')", $resource->getSecurity());
        self::assertSame('/admin', $resource->getRoutePrefix());
    }

    /** @param class-string $class */
    #[DataProvider('resources')]
    public function testOperationsDoNotOverrideTheSecurity(string $class, string $permission): void
    {
        $operations = self::resource($class)->getOperations();

        self::assertNotNull($operations);
        self::assertGreaterThan(0, \count($operations));
        foreach ($operations as $name => $operation) {
            self::assertNull($operation->getSecurity(), \sprintf('%s operation %s', $class, $name));
        }
    }

    /** @param class-string $class */
    #[DataProvider('resourcesWithRoles')]
    public function testGrantedWithTheMappedRole(string $class, string $mapped, string $other): void
    {
        self::assertTrue($this->evaluate($class, self::userToken([$mapped]), self::MAP));
    }

    /** @param class-string $class */
    #[DataProvider('resourcesWithRoles')]
    public function testDeniedWithAForeignRole(string $class, string $mapped, string $other): void
    {
        self::assertFalse($this->evaluate($class, self::userToken([self::ROLE_OTHER]), self::MAP));
    }

    /** @param class-string $class */
    #[DataProvider('resourcesWithRoles')]
    public function testDeniedWithTheRoleOfTheOtherPermission(string $class, string $mapped, string $other): void
    {
        self::assertFalse($this->evaluate($class, self::userToken([$other]), self::MAP));
    }

    /** @param class-string $class */
    #[DataProvider('resources')]
    public function testDeniedWhenTheMapLacksThePermission(string $class, string $permission): void
    {
        $map = self::MAP;
        unset($map[$permission]);

        self::assertFalse($this->evaluate($class, self::userToken([self::ROLE_OPS, self::ROLE_DBA]), $map));
    }

    /** @param class-string $class */
    #[DataProvider('resources')]
    public function testDeniedWhenTheRoleListIsEmpty(string $class, string $permission): void
    {
        $map = self::MAP;
        $map[$permission] = [];

        self::assertFalse($this->evaluate($class, self::userToken([self::ROLE_OPS, self::ROLE_DBA]), $map));
    }

    /** @param class-string $class */
    #[DataProvider('resources')]
    public function testDeniedWithoutUser(string $class, string $permission): void
    {
        self::assertFalse($this->evaluate($class, new NullToken(), self::MAP));
    }

    public function testRoleHierarchyIsHonoured(): void
    {
        $token = self::userToken([self::ROLE_LEAD]);

        self::assertTrue($this->evaluate(MessengerMessage::class, $token, self::MAP));
        self::assertFalse($this->evaluate(DoctrineMigrationVersion::class, $token, self::MAP));
    }

    public function testUnregisteredPermissionIsDeniedBecauseAllVotersAbstain(): void
    {
        $map = self::MAP + ['sysres:unknown' => [self::ROLE_OPS]];
        $checker = self::checker(self::userToken([self::ROLE_OPS, self::ROLE_DBA]), $map);

        self::assertFalse($checker->isGranted(
            MessengerMessage::class,
            "is_granted('sysres:unknown')",
            ['object' => null],
        ));
    }

    /**
     * @param class-string                $class
     * @param array<string, list<string>> $map
     */
    private function evaluate(string $class, TokenInterface $token, array $map): bool
    {
        $security = self::resource($class)->getSecurity();
        self::assertNotNull($security);

        // Subject null: the collection and Hydra filter case.
        return self::checker($token, $map)->isGranted($class, $security, ['object' => null]);
    }

    /** @param array<string, list<string>> $map */
    private static function checker(TokenInterface $token, array $map): ResourceAccessChecker
    {
        $roleHierarchy = new RoleHierarchy(self::HIERARCHY);
        $tokenStorage = new TokenStorage();
        $tokenStorage->setToken($token);

        $voter = new MapPermissionVoter(
            $roleHierarchy,
            [
                SystemResourcesPermissions::MESSENGER_READ,
                SystemResourcesPermissions::MIGRATION_READ,
            ],
            $map,
        );
        $authorizationChecker = new AuthorizationChecker(
            $tokenStorage,
            new AccessDecisionManager([$voter], new AffirmativeStrategy(false)),
        );

        return new ResourceAccessChecker(
            new ExpressionLanguage(),
            new AuthenticationTrustResolver(),
            $roleHierarchy,
            $tokenStorage,
            $authorizationChecker,
        );
    }

    /** @param list<string> $roles */
    private static function userToken(array $roles): UsernamePasswordToken
    {
        return new UsernamePasswordToken(
            new InMemoryUser('sysres-test', null, $roles),
            'main',
            $roles,
        );
    }

    /** @param class-string $class */
    private static function resource(string $class): ApiResource
    {
        return (new \ReflectionClass($class))
            ->getAttributes(ApiResource::class)[0]
            ->newInstance();
    }
}
