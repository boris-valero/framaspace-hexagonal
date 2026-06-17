<?php

declare(strict_types=1);

namespace OCA\FramaSpace\Domain\Service;

use OCA\FramaSpace\Domain\Port\CacheService;
use OCA\FramaSpace\Domain\Port\MetricsRepository;

class MetricsAggregator {
	private const CACHE_TTL_SECONDS = 6 * 3600;
	private const CACHE_KEY = 'all-metrics-v1';

	public function __construct(
		private MetricsRepository $repository,
		private CacheService $cache,
	) {
	}

	public function getAll(): array {
		if ($this->cache->has(self::CACHE_KEY)) {
			/** @var mixed $cached */
			$cached = $this->cache->get(self::CACHE_KEY);
			if (is_array($cached)) {
				return $cached;
			}
		}

		$metrics = $this->repository->getAll();

		$this->cache->set(self::CACHE_KEY, $metrics, self::CACHE_TTL_SECONDS);

		return $metrics;
	}
}
