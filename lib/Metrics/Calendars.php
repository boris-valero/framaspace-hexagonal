<?php

declare(strict_types=1);

namespace OCA\FramaSpace\Metrics;

use OCP\DB\QueryBuilder\IQueryBuilder;

class Calendars extends BaseMetrics {
	public function countCalendars(): int {
		return $this->executeCount('calendars', 'calendar_count');
	}

	public function countAddressbooks(): int {
		return $this->executeCount('addressbooks', 'addressbook_count');
	}

	public function countContacts(): int {
		return $this->executeCount('cards', 'contact_count');
	}

	public function countEvents(): int {
		return $this->executeCount('calendarobjects', 'event_count', function (IQueryBuilder $qb) {
			$qb->where($qb->expr()->eq('componenttype', $qb->createNamedParameter('VEVENT')));
		});
	}

	public function countTasks(): int {
		return $this->executeCount('calendarobjects', 'task_count', function (IQueryBuilder $qb) {
			$qb->where($qb->expr()->eq('componenttype', $qb->createNamedParameter('VTODO')));
		});
	}

	public function getMetrics(): array {
		return [
			'calendars' => $this->countCalendars(),
			'addressbooks' => $this->countAddressbooks(),
			'contacts' => $this->countContacts(),
			'events' => $this->countEvents(),
			'tasks' => $this->countTasks()
		];
	}
}
