<?php

declare(strict_types=1);

namespace OCA\FramaSpace\Metrics;

class Conversations extends BaseMetrics {
	public function countConversations(): int {
		return $this->executeCount('talk_rooms', 'conversation_count');
	}

	public function getMetrics(): array {
		return [
			'conversations' => $this->countConversations()
		];
	}
}
