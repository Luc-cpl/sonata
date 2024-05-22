<?php

namespace Sonata\Middleware;

use Orkestra\Services\Http\Middleware\AbstractMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sonata\Authorization;
use League\Route\Http\Exception\UnauthorizedException;

class AuthorizationMiddleware extends AbstractMiddleware
{
	public function __construct(
		private ?string $guard = null,
	) {
		//
	}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$auth = $this->app->get(Authorization::class);
		if ($this->guard !== null) {
			$auth = $auth->guard($this->guard);
		}

		if (!$auth->check()) {
			throw new UnauthorizedException();
		}

		return $handler->handle($request);
	}
}