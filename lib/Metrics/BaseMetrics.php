<?php

declare(strict_types=1);

namespace OCA\FramaSpace\Metrics;

use OCA\FramaSpace\Config\MetricsConfig;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

abstract class BaseMetrics {
	public function __construct(
		protected IDBConnection $db,
	) {
	}

	/**
	 * Execute a COUNT query on a table
	 *
	 * @param string $table The table name to count from
	 * @param string $countAlias The alias for the COUNT result
	 * @param (callable(IQueryBuilder): void)|null $addWhere Optional callback to add WHERE conditions
	 * @return int The count result
	 */
	protected function executeCount(
		string $table,
		string $countAlias,
		?callable $addWhere = null,
	): int {
		$qb = $this->db->getQueryBuilder();
		$qb->select($qb->func()->count($countAlias))
			->from($table);

		if ($addWhere !== null) {
			$addWhere($qb);
		}

		$result = $qb->executeQuery();
		/** @var false|int */
		$row = $result->fetchOne();
		$result->closeCursor();
		return ($row === false ? 0 : $row);
	}

	/**
	 * Extract username from storage identifier
	 *
	 * @param string $storageId The storage identifier (e.g., 'home::user' or 'object::user:user')
	 * @return string The extracted username
	 */
	public static function extractUsername(string $storageId): string {
		return preg_replace('/^(?:home|object::user)::/', '', $storageId) ?? '';
	}

	/**
	 * Join filecache with storages table
	 *
	 * @param IQueryBuilder $qb The query builder
	 * @return void
	 */
	protected function joinStorages(IQueryBuilder $qb): void {
		$qb->innerJoin('f', 'storages', 's', $qb->expr()->eq('f.storage', 's.numeric_id'));
	}

	/**
	 * Join filecache with mimetypes table
	 *
	 * @param IQueryBuilder $qb The query builder
	 * @return void
	 */
	protected function joinMimetypes(IQueryBuilder $qb): void {
		$qb->innerJoin('f', 'mimetypes', 'm', $qb->expr()->eq('f.mimetype', 'm.id'));
	}

	/**
	 * Execute query and fetch single row result
	 *
	 * @param IQueryBuilder $qb The query builder
	 * @return int The fetched row value or 0 if no result
	 */
	protected function executeFetchOne(IQueryBuilder $qb): int {
		$result = $qb->executeQuery();
		/** @var false|int|null */
		$row = $result->fetchOne();
		$result->closeCursor();
		return ($row === false || $row === null ? 0 : $row);
	}

	/**
	 * Execute query and fetch all rows result
	 *
	 * @param IQueryBuilder $qb The query builder
	 * @return array The fetched rows
	 */
	protected function executeFetchAll(IQueryBuilder $qb): array {
		$result = $qb->executeQuery();
		$rows = $result->fetchAll();
		$result->closeCursor();
		return $rows;
	}

	/**
	 * Apply storage pattern filter to query (home, object::user, local, amazon)
	 *
	 * @param IQueryBuilder $qb The query builder
	 * @return void
	 */
	protected function applyStoragePatternFilter(IQueryBuilder $qb): void {
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

	/**
	 * Get all metrics for this feature
	 *
	 * @return array The metrics array
	 */
	abstract public function getMetrics(): array;
}
