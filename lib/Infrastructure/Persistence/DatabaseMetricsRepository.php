<?php

declare(strict_types=1);

namespace OCA\FramaSpace\Infrastructure\Persistence;

use OCA\FramaSpace\Config\MetricsConfig;
use OCA\FramaSpace\Domain\Port\MetricsRepository;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class DatabaseMetricsRepository implements MetricsRepository {
	public function __construct(
		private IDBConnection $db,
	) {
	}

	public function getAll(): array {
		return [
			'deck' => $this->getDeckMetrics(),
			'tables' => $this->getTablesMetrics(),
			'forms' => $this->getFormsMetrics(),
			'collectives' => $this->getCollectivesMetrics(),
			'circles' => $this->getCirclesMetrics(),
			'calendars' => $this->getCalendarsMetrics(),
			'talk' => array_merge(
				$this->getConversationsMetrics(),
				$this->getChatsMetrics(),
			),
			'filecache' => $this->getFilecacheMetrics(),
		];
	}

	private function getDeckMetrics(): array {
		return [
			'cards' => $this->countTable('deck_cards', 'card_count'),
			'boards' => $this->countTable('deck_boards', 'board_count'),
			'stacks' => $this->countTable('deck_stacks', 'stack_count'),
		];
	}

	private function getTablesMetrics(): array {
		return [
			'tables' => $this->countTable('tables_tables', 'table_count'),
			'rows' => $this->countTable('tables_rows', 'row_count'),
		];
	}

	private function getFormsMetrics(): array {
		return [
			'forms' => $this->countTable('forms_v2_forms', 'form_count'),
			'submissions' => $this->countTable('forms_v2_submissions', 'submission_count'),
		];
	}

	private function getCollectivesMetrics(): array {
		return [
			'collectives' => $this->countTable('collectives', 'collectives_number'),
			'pages' => $this->countTable('collectives_pages', 'page_count'),
		];
	}

	private function getCirclesMetrics(): array {
		return [
			'circles' => $this->countTable('circles_circle', 'circle_count'),
		];
	}

	private function getCalendarsMetrics(): array {
		return [
			'calendars' => $this->countTable('calendars', 'calendar_count'),
			'addressbooks' => $this->countTable('addressbooks', 'addressbook_count'),
			'contacts' => $this->countTable('cards', 'contact_count'),
			'events' => $this->countWithWhere('calendarobjects', 'event_count', 'componenttype', 'VEVENT'),
			'tasks' => $this->countWithWhere('calendarobjects', 'task_count', 'componenttype', 'VTODO'),
		];
	}

	private function getConversationsMetrics(): array {
		return [
			'conversations' => $this->countTable('talk_rooms', 'conversation_count'),
		];
	}

	private function getChatsMetrics(): array {
		return [
			'messages' => $this->countWithWhere('comments', 'chat_count', 'object_type', 'chat'),
		];
	}

	private function getFilecacheMetrics(): array {
		return [
			'storage' => $this->getTotalStorageSize(),
			'files' => $this->countFiles(),
			'topUsers' => $this->getTopStorageUsers(),
			'topBiggestFiles' => $this->getTopBiggestFiles(),
			'versionsStorage' => $this->getTotalVersionsStorage(),
			'topBiggestTrash' => $this->getTopTrashByUser(),
		];
	}

	private function countTable(string $table, string $alias): int {
		$qb = $this->db->getQueryBuilder();
		$qb->select($qb->func()->count($alias))
			->from($table);

		$result = $qb->executeQuery();
		$row = $result->fetchOne();
		$result->closeCursor();
		return ($row === false ? 0 : $row);
	}

	private function countWithWhere(string $table, string $alias, string $field, string $value): int {
		$qb = $this->db->getQueryBuilder();
		$qb->select($qb->func()->count($alias))
			->from($table)
			->where($qb->expr()->eq($field, $qb->createNamedParameter($value)));

		$result = $qb->executeQuery();
		$row = $result->fetchOne();
		$result->closeCursor();
		return ($row === false ? 0 : $row);
	}

	private function executeFetchOne(IQueryBuilder $qb): int {
		$result = $qb->executeQuery();
		$row = $result->fetchOne();
		$result->closeCursor();
		return ($row === false || $row === null ? 0 : $row);
	}

	private function executeFetchAll(IQueryBuilder $qb): array {
		$result = $qb->executeQuery();
		$rows = $result->fetchAll();
		$result->closeCursor();
		return $rows;
	}

	private function joinStorages(IQueryBuilder $qb): void {
		$qb->innerJoin('f', 'storages', 's', $qb->expr()->eq('f.storage', 's.numeric_id'));
	}

	private function joinMimetypes(IQueryBuilder $qb): void {
		$qb->innerJoin('f', 'mimetypes', 'm', $qb->expr()->eq('f.mimetype', 'm.id'));
	}

	private function applyStoragePatternFilter(IQueryBuilder $qb): void {
		$patterns = [
			MetricsConfig::STORAGE_HOME_PATTERN,
			MetricsConfig::STORAGE_OBJECT_USER_PATTERN,
			MetricsConfig::STORAGE_LOCAL_PATTERN,
			MetricsConfig::STORAGE_OBJECT_AMAZON_PATTERN,
		];

		$conditions = [];
		foreach ($patterns as $pattern) {
			$conditions[] = $qb->expr()->like('s.id', $qb->createNamedParameter($pattern, IQueryBuilder::PARAM_STR));
		}

		$qb->andWhere($qb->expr()->orX(...$conditions));
	}

	private static function extractUsername(string $storageId): string {
		return preg_replace('/^(?:home|object::user)::/', '', $storageId) ?? '';
	}

	private function getTotalStorageSize(): int {
		$qb = $this->db->getQueryBuilder();
		$qb->select($qb->func()->sum('f.size'))
			->from('filecache', 'f');

		$this->joinStorages($qb);

		$qb->where($qb->expr()->eq('f.path', $qb->createNamedParameter('')));

		$this->applyStoragePatternFilter($qb);

		return $this->executeFetchOne($qb);
	}

	private function countFiles(): int {
		$qb = $this->db->getQueryBuilder();
		$qb->select($qb->func()->count('*'))
			->from('filecache', 'f');

		$this->joinMimetypes($qb);

		$qb->where($qb->expr()->neq('m.mimetype', $qb->createNamedParameter('httpd/unix-directory')));
		return $this->executeFetchOne($qb);
	}

	private function getTopStorageUsers(): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('s.id')
			->selectAlias(
				$qb->func()->sum('f.size'),
				'total_size',
			)
			->from('filecache', 'f');

		$this->joinStorages($qb);
		$this->joinMimetypes($qb);

		$qb->where($qb->expr()->like('s.id', $qb->createNamedParameter(MetricsConfig::STORAGE_HOME_PATTERN, IQueryBuilder::PARAM_STR)))
			->andWhere($qb->expr()->neq('m.mimetype', $qb->createNamedParameter('httpd/unix-directory')))
			->andWhere($qb->expr()->like('f.path', $qb->createNamedParameter(MetricsConfig::STORAGE_FILES_PATH_PATTERN, IQueryBuilder::PARAM_STR)))
			->groupBy('s.id')
			->orderBy('total_size', 'DESC')
			->setMaxResults(MetricsConfig::N_TOP_STORAGE_USERS);

		$rows = $this->executeFetchAll($qb);
		$users = [];
		foreach ($rows as $row) {
			$users[] = [
				'username' => self::extractUsername($row['id']),
				'size_bytes' => ($row['total_size'] ?? 0),
			];
		}
		return $users;
	}

	private function getTopBiggestFiles(): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('f.name', 'f.size', 'f.path', 's.id')
			->from('filecache', 'f');

		$this->joinStorages($qb);
		$this->joinMimetypes($qb);

		$qb->where($qb->expr()->neq('m.mimetype', $qb->createNamedParameter('httpd/unix-directory')))
			->andWhere($qb->expr()->like('s.id', $qb->createNamedParameter(MetricsConfig::STORAGE_HOME_PATTERN, IQueryBuilder::PARAM_STR)))
			->andWhere($qb->expr()->like('f.path', $qb->createNamedParameter(MetricsConfig::STORAGE_FILES_PATH_PATTERN, IQueryBuilder::PARAM_STR)))
			->orderBy('f.size', 'DESC')
			->setMaxResults(MetricsConfig::N_TOP_BIGGEST_FILES);

		$rows = $this->executeFetchAll($qb);
		$files = [];
		foreach ($rows as $row) {
			$files[] = [
				'filename' => ($row['name'] ?? ''),
				'size_bytes' => ($row['size'] ?? 0),
				'path' => ($row['path'] ?? ''),
				'owner' => self::extractUsername($row['id']),
			];
		}
		return $files;
	}

	private function getTotalVersionsStorage(): int {
		$qb = $this->db->getQueryBuilder();
		$qb->select($qb->func()->sum('f.size'))
			->from('filecache', 'f');

		$this->joinMimetypes($qb);

		$qb->where($qb->expr()->neq('m.mimetype', $qb->createNamedParameter('httpd/unix-directory')))
			->andWhere($qb->expr()->like('f.path', $qb->createNamedParameter(MetricsConfig::STORAGE_VERSIONS_PATH_PATTERN, IQueryBuilder::PARAM_STR)));
		return $this->executeFetchOne($qb);
	}

	private function getTopTrashByUser(): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('s.id')
			->selectAlias($qb->func()->count('f.fileid'), 'files_count')
			->selectAlias($qb->func()->sum('f.size'), 'total_size')
			->from('filecache', 'f');

		$this->joinStorages($qb);
		$this->joinMimetypes($qb);

		$qb->where($qb->expr()->like('s.id', $qb->createNamedParameter(MetricsConfig::STORAGE_HOME_PATTERN, IQueryBuilder::PARAM_STR)))
			->andWhere($qb->expr()->neq('m.mimetype', $qb->createNamedParameter('httpd/unix-directory')))
			->andWhere($qb->expr()->like('f.path', $qb->createNamedParameter(MetricsConfig::STORAGE_TRASHBIN_PATH_PATTERN, IQueryBuilder::PARAM_STR)))
			->groupBy('s.id')
			->orderBy('total_size', 'DESC')
			->setMaxResults(MetricsConfig::N_TOP_TRASH_USERS);

		$rows = $this->executeFetchAll($qb);
		$users = [];
		foreach ($rows as $row) {
			$users[] = [
				'username' => self::extractUsername($row['id']),
				'files_count' => ($row['files_count'] ?? 0),
				'trash_bytes' => ($row['total_size'] ?? 0),
			];
		}
		return $users;
	}
}
