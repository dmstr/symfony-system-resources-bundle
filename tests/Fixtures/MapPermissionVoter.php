<?php
// file generated with AI assistance: Claude Code - 2026-10-28 00:56:44 UTC

declare(strict_types=1);

namespace Dmstr\SymfonySystemResources\Tests\Fixtures;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * Test-only copy of the za7-core permission voter contract.
 *
 * The real voter lives in za7-core (`Core\Security\PermissionVoter`); this
 * bundle must not depend on za7-core, so its tests use this dependency-free
 * copy to evaluate `is_granted('<bundle>:<action>')` expressions.
 *
 * - Only registered permissions are supported; every other attribute abstains.
 * - The subject is ignored and every subject type is supported, including null.
 * - Fail-closed: no user, an unmapped permission or an empty role list deny.
 * - Never throws.
 *
 * @extends Voter<string, mixed>
 */
final class MapPermissionVoter extends Voter
{
    /**
     * @param list<string>                $registered registered permission strings
     * @param array<string, list<string>> $map        permission => roles that grant it
     */
    public function __construct(
        private readonly RoleHierarchyInterface $roleHierarchy,
        private readonly array $registered,
        private readonly array $map,
    ) {
    }

    public function supportsAttribute(string $attribute): bool
    {
        return \in_array($attribute, $this->registered, true);
    }

    public function supportsType(string $subjectType): bool
    {
        return true;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $this->supportsAttribute($attribute);
    }

    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token,
        ?Vote $vote = null,
    ): bool {
        if (null === $token->getUser()) {
            return false;
        }

        $granting = $this->map[$attribute] ?? [];
        if ([] === $granting) {
            return false;
        }

        $reachable = $this->roleHierarchy->getReachableRoleNames(
            $token->getRoleNames(),
        );

        return [] !== array_intersect($granting, $reachable);
    }
}
