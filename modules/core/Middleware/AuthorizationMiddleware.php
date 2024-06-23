<?php

namespace Sonata\Middleware;

use Orkestra\Services\Http\Middleware\AbstractMiddleware;
use League\Route\Http\Exception\UnauthorizedException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sonata\Authorization;

/**
 * Middleware to authorize the access to a route.
 *
 * @template T of object
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

        $isLoggedIn = $auth->check();

        if ($this->guest && $isLoggedIn) {
            throw new UnauthorizedException();
        }

        if (!$this->guest && !$isLoggedIn) {
            throw new UnauthorizedException();
        }

        return $handler->handle($request);
    }
}
