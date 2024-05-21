<?php

namespace Sonata;

use Orkestra\Interfaces\ConfigurationInterface;
use Sonata\Interfaces\AuthDriverInterface;
use Sonata\Interfaces\UserRepositoryInterface;
use Orkestra\Interfaces\AppContainerInterface;
use InvalidArgumentException;
use Sonata\Entities\Abstracts\AbstractUser;
use Sonata\Interfaces\AuthInterface;

class Authorization implements AuthInterface
{
	/**
	 * @var AuthDriverInterface[]
	 */
	private array $instances = [];

	private string $currentGuard;

	public function __construct(
		private ConfigurationInterface $config,
		private AppContainerInterface $app
	) {
		$this->currentGuard = $this->config->get('sonata.default_guard');
	}

	/**
	 * Retrieves the guard instance for the given guard key.
	 * Usually this method should be called by a middleware to retrieve the
	 * guard instance for the current request.
	 */
	public function guard(string $guard): AuthDriverInterface
	{
		$this->currentGuard = $guard;

		if (array_key_exists($guard, $this->instances)) {
			return $this->instances[$guard];
		}

		$guards = $this->config->get('sonata.auth_guards');
		if (!array_key_exists($guard, $guards)) {
			throw new InvalidArgumentException("The guard \"$guard\" does not exist");
		}

		$guardKey   = $guard;
		$guard      = $guards[$guard];
		$driver     = $guard['driver'];
		$repository = $guard['repository'];

		/** @var AuthDriverInterface */
		$driver = $this->app->make($driver);
		if (!is_subclass_of($driver, AuthDriverInterface::class)) {
			throw new InvalidArgumentException("The guard driver must implement " . AuthDriverInterface::class);
		}

		/** @var UserRepositoryInterface */
		$repository = $this->app->get($repository);
		if (!is_subclass_of($repository, UserRepositoryInterface::class)) {
			throw new InvalidArgumentException("The guard repository must implement " . UserRepositoryInterface::class);
		}

		$driver->setRepository($repository);
		$driver->setGuard($guardKey);

		$this->instances[$guardKey] = $driver;

		return $driver;
	}

	public function user(): AbstractUser
	{
		return $this->guard($this->currentGuard)->user();
	}

	public function check(): bool
	{
		return $this->guard($this->currentGuard)->check();
	}

	public function authenticate(AbstractUser $user): void
	{
		$this->guard($this->currentGuard)->authenticate($user);
	}

	public function logout(): void
	{
		$this->guard($this->currentGuard)->logout();
	}
}