<?php

declare(strict_types=1);

namespace OCA\FramaSpace\Metrics;

use OCA\FramaSpace\Config\MetricsConfig;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class Filecache extends BaseMetrics {
	public function __construct(IDBConnection $connection) {
		parent::__construct($connection);
	}

	public function getTotalStorageSize(): int {
		$qb = $this->db->getQueryBuilder();
		$qb->select($qb->func()->sum('f.size'))
			->from('filecache', 'f');

		$this->joinStorages($qb);

		$qb->where($qb->expr()->eq('f.path', $qb->createNamedParameter('')));

		$this->applyStoragePatternFilter($qb);

		return $this->executeFetchOne($qb);
	}

	public function countFiles(): int {
		$qb = $this->db->getQueryBuilder();
		$qb->select($qb->func()->count('*'))
			->from('filecache', 'f');

		$this->joinMimetypes($qb);

		$qb->where($qb->expr()->neq('m.mimetype', $qb->createNamedParameter('httpd/unix-directory')));
		return $this->executeFetchOne($qb);
	}

	/**
	 * @return list<array{username: string, size_bytes: int}>
	 */
	public function getTopStorageUsers(): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('s.id')
			->selectAlias(
				$qb->func()->sum('f.size'),
				'total_size'
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

		/** @var list<array{id: string, total_size: int}> */
		$rows = $this->executeFetchAll($qb);
		$users = [];
		foreach ($rows as $row) {
			$users[] = [
				'username' => BaseMetrics::extractUsername($row['id']),
				'size_bytes' => ($row['total_size'] ?? 0),
			];
		}
		return $users;
	}

	/**
	 * @return list<array{filename: string, size_bytes: int, path: string, owner: string}>
	 */
	public function getTopBiggestFiles(): array {
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

		/** @var list<array{name: string, size: int, path: string, id: string}> */
		$rows = $this->executeFetchAll($qb);
		$files = [];
		foreach ($rows as $row) {
			$files[] = [
				'filename' => ($row['name'] ?? ''),
				'size_bytes' => ($row['size'] ?? 0),
				'path' => ($row['path'] ?? ''),
				'owner' => BaseMetrics::extractUsername($row['id']),
			];
		}
		return $files;
	}

	public function getTotalVersionsStorage(): int {
		$qb = $this->db->getQueryBuilder();
		$qb->select($qb->func()->sum('f.size'))
			->from('filecache', 'f');

		$this->joinMimetypes($qb);

		$qb->where($qb->expr()->neq('m.mimetype', $qb->createNamedParameter('httpd/unix-directory')))
			->andWhere($qb->expr()->like('f.path', $qb->createNamedParameter(MetricsConfig::STORAGE_VERSIONS_PATH_PATTERN, IQueryBuilder::PARAM_STR)));
		return $this->executeFetchOne($qb);
	}

	/**
	 * @return list<array{username: string, files_count: int, trash_bytes: int}>
	 */
	public function getTopTrashByUser(): array {
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

		/** @var list<array{files_count: int, total_size: int, id: string}> */
		$rows = $this->executeFetchAll($qb);
		$users = [];
		foreach ($rows as $row) {
			$users[] = [
				'username' => BaseMetrics::extractUsername($row['id']),
				'files_count' => ($row['files_count'] ?? 0),
				'trash_bytes' => ($row['total_size'] ?? 0),
			];
		}
		return $users;
	}

	public function getMetrics(): array {
		return [
			'storage' => $this->getTotalStorageSize(),
			'files' => $this->countFiles(),
			'topUsers' => $this->getTopStorageUsers(),
			'topBiggestFiles' => $this->getTopBiggestFiles(),
			'versionsStorage' => $this->getTotalVersionsStorage(),
			'topBiggestTrash' => $this->getTopTrashByUser(),
		];
	}
}
