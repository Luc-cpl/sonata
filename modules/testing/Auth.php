<?php

namespace Sonata\Testing;

use Sonata\Authorization\Authorization;
use Sonata\Authorization\Interfaces\AuthInterface;
use Sonata\Entities\Interfaces\IdentifiableInterface;

class Auth
{
    /**
     * @var (string|null)[]
     */
    private static array $usedGuards = [];

    /**
     * Authenticate as the given user for testing purposes.
     *
     * @template TUser of IdentifiableInterface
     * @param TUser $user
     * @param class-string|string|null $guard
     */
    public static function actingAs(IdentifiableInterface $user, ?string $guard = null): void
    {
        self::$usedGuards[] = $guard;
        self::$usedGuards = array_unique(self::$usedGuards);
        self::guard($guard)->authenticate($user);
    }

    /**
     * Retrieve the guard instance for the given guard key.
     *
     * @return AuthInterface<IdentifiableInterface>
     */
    public static function guard(?string $guard = null): AuthInterface
    {
        self::$usedGuards[] = $guard ?? null;
        self::$usedGuards = array_unique(self::$usedGuards);
        $auth = app()->get(Authorization::class);
        $auth = $guard !== null ? $auth->guard($guard) : $auth;
        return $auth;
    }

    /**
     * revoke all authentication guards used in the tests.
     */
    public static function clear(): void
    {
        foreach (self::$usedGuards as $guard) {
            self::guard($guard)->revoke();
        }
        self::$usedGuards = [];
    }
}
