<?php

declare(strict_types=1);

namespace OCA\FramaSpace\Metrics;

class Circles extends BaseMetrics {
	public function countCircles(): int {
		return $this->executeCount('circles_circle', 'circle_count');
	}

	public function getMetrics(): array {
		return [
			'circles' => $this->countCircles()
		];
	}
}
