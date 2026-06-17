<?php

declare(strict_types=1);

namespace OCA\FramaSpace\Domain\Port;

interface CacheService {
	public function get(string $key): mixed;

	public function set(string $key, mixed $value, int $ttl): void;

	public function has(string $key): bool;
}
