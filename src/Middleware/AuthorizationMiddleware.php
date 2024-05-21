<?php

namespace Sonata\Middleware;

use Orkestra\Services\Http\Middleware\AbstractMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthorizationMiddleware extends AbstractMiddleware
{
	public function __construct(
		private string $guard,
		private string $role
	) {
		//
	}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$user = $this->app->get('auth')->guard($this->guard)->user();

		if (!$user || !$user->hasRole($this->role)) {
			throw new \Exception('Unauthorized', 401);
		}

		return $handler->handle($request);
	}
}