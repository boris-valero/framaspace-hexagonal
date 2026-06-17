<?php

declare(strict_types=1);

namespace OCA\FramaSpace\Infrastructure\Cache;

use OCA\FramaSpace\Domain\Port\CacheService;
use OCP\ICacheFactory;

class NextcloudCacheService implements CacheService {
	private const CACHE_NAMESPACE = 'framaspace-stats';

	public function __construct(
		private ICacheFactory $cacheFactory,
	) {
	}

	public function get(string $key): mixed {
		return $this->cacheFactory->createLocal(self::CACHE_NAMESPACE)->get($key);
	}

	public function set(string $key, mixed $value, int $ttl): void {
		$this->cacheFactory->createLocal(self::CACHE_NAMESPACE)->set($key, $value, $ttl);
	}

	public function has(string $key): bool {
		return $this->cacheFactory->createLocal(self::CACHE_NAMESPACE)->hasKey($key);
	}
}
