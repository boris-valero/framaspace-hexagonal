<?php

declare(strict_types=1);

namespace OCA\FramaSpace\Metrics;

class Deck extends BaseMetrics {
	public function countCards(): int {
		return $this->executeCount('deck_cards', 'card_count');
	}

	public function countBoards(): int {
		return $this->executeCount('deck_boards', 'board_count');
	}

	public function countStacks(): int {
		return $this->executeCount('deck_stacks', 'stack_count');
	}

	public function getMetrics(): array {
		return [
			'cards' => $this->countCards(),
			'boards' => $this->countBoards(),
			'stacks' => $this->countStacks()
		];
	}
}
