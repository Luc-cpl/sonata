<?php

namespace Sonata\Doctrine;

use Orkestra\Interfaces\ConfigurationInterface;

/**
 * Class ListenersRegistry
 *
 * This class is used to register event listeners to be used by the Doctrine ORM.
 * It allows easy registration of custom listeners from different providers.
 */
class ListenersRegistry
{
	/**
	 * @var array<string|class-string> The list of registered listeners.
	 */
	private static array $listeners = [];

	public function __construct(
		private ConfigurationInterface $config
	) {
		//
	}

	/**
	 * Retrieves the list of registered listeners.
	 *
	 * @return string[]|class-string[]
	 */
	public function getListeners(): array
	{
		return self::$listeners;
	}

	/**
	 * Registers an listener to be used by the Doctrine ORM.
	 *
	 * @param string $event The Doctrine event name
	 * @param string|class-string $listener The listener class name
	 */
	public static function register(string $event, string $listener): void
	{
		self::$listeners[$event] = $listener;
	}
}