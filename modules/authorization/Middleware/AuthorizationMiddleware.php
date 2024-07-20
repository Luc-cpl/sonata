<?php

namespace Sonata\Authorization\Middleware;

use League\Route\Http\Exception\UnauthorizedException;
use Orkestra\Services\Http\Middleware\AbstractMiddleware;
use Sonata\Authorization\Authorization;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sonata\Entities\Interfaces\IdentifiableInterface;

/**
 * Middleware to authorize the access to a route.
 *
 * @template T of IdentifiableInterface
 */
class AuthorizationMiddleware extends AbstractMiddleware
{
    /**
     * @param Authorization<T> $auth
     */
    public function __construct(
        private Authorization $auth,
        private ?string $guard = null,
        private bool $guest = false
    ) {
        //
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $auth = $this->auth;
        if ($this->guard !== null) {
            $auth = $auth->guard($this->guard);
        }

        $isLoggedIn = $auth->user() !== null;

        if ($this->guest && $isLoggedIn) {
            throw new UnauthorizedException('User is already logged in');
        }

        if (!$this->guest && !$isLoggedIn) {
            throw new UnauthorizedException('User is not logged in');
        }

        return $handler->handle($request);
    }
}
