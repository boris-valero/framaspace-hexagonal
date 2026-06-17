<?php

declare(strict_types=1);

namespace OCA\FramaSpace\Metrics;

class Collectives extends BaseMetrics {
	public function countCollectives(): int {
		return $this->executeCount('collectives', 'collectives_number');
	}

	public function countPages(): int {
		return $this->executeCount('collectives_pages', 'page_count');
	}

	public function getMetrics(): array {
		return [
			'collectives' => $this->countCollectives(),
			'pages' => $this->countPages()
		];
	}
}
