<?php

declare(strict_types=1);

namespace OCA\FramaSpace\Metrics;

use OCP\DB\QueryBuilder\IQueryBuilder;

class Chats extends BaseMetrics {
	public function countChats(): int {
		return $this->executeCount('comments', 'chat_count', function (IQueryBuilder $qb) {
			$qb->where($qb->expr()->eq('object_type', $qb->createNamedParameter('chat')));
		});
	}

	public function getMetrics(): array {
		return [
			'messages' => $this->countChats()
		];
	}
}
