<?php

declare(strict_types=1);

namespace OCA\FramaSpace\Domain\Port;

interface MetricsRepository {
	public function getAll(): array;
}
