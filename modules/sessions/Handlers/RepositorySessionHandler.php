<?php

namespace Sonata\Sessions\Handlers;

use Sonata\Sessions\Interfaces\SessionRepositoryInterface;
use Sonata\Sessions\SessionDrivers;
use Sonata\Authorization\Authorization;
use Sonata\Entities\Interfaces\IdentifiableInterface;
use Psr\Http\Message\ServerRequestInterface;
use SessionHandlerInterface;
use DateTime;

class RepositorySessionHandler implements SessionHandlerInterface
{
	/**
	 * @param Authorization<IdentifiableInterface> $authorization
	 */
	public function __construct(
		private SessionDrivers $drivers,
		private SessionRepositoryInterface $repository,
		private ?ServerRequestInterface $request = null,
		private ?Authorization $authorization = null,
	) {
		//
	}

	public function close(): bool
	{
		return true;
	}

	public function open(string $path, string $name): bool
	{
		return true;
	}

	public function destroy(string $sessionId): bool
	{
		$session = $this->repository->get($sessionId);
		if ($session !== null) {
			$this->repository->delete($session);
		}
		return true;
	}

	public function gc(int $lifetime): int|false
	{
		$repository = $this->repository->updatedBefore(new DateTime("-$lifetime seconds"));

		$driver = $this->drivers->current();
		if ($driver) {
			$repository = $repository->whereDriver($driver);
		}

		$count = 0;
		$iterator = $repository->getIterator();
		foreach ($iterator as $session) {
			$repository->delete($session);
			$count++;
		}

		return $count > 0 ? $count : false;
	}

	public function read(string $sessionId): string
	{
		$session = $this->repository->get($sessionId);
		if ($session === null) {
			return '';
		}
		return $this->repository->get($sessionId)?->data ?? '';
	}

	public function write(string $sessionId, string $data): bool
	{
		$session = $this->repository->get($sessionId);
		if ($session === null) {
			$session = $this->repository->make([
				'id' => $sessionId,
				'driver' => $this->drivers->current() ?? '',
			]);
		}

		$guardName = $this->authorization?->getActiveGuardName() ?? '';
		$guardOptions = $this->authorization?->getGuardOptions($guardName) ?? [];

		if ($this->authorization && ($guardOptions['driver'] ?? '') === $this->drivers->current()) {
			$session->set(userId: $this->authorization->user()?->getId());
		}

		$session->set(
			data: $data,
			updatedAt: new DateTime(),
			ip: $this->request?->getServerParams()['REMOTE_ADDR'] ?? '',
			userAgent: $this->request?->getServerParams()['HTTP_USER_AGENT'] ?? '',
		);

		$this->repository->persist($session);
		return true;
	}
}