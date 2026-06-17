<?php

declare(strict_types=1);

namespace OCA\FramaSpace\Config;

class MetricsConfig {
	public const N_TOP_BIGGEST_FILES = 10;
	public const N_TOP_TRASH_USERS = 3;
	public const N_TOP_STORAGE_USERS = 5;

	public const STORAGE_HOME_PATTERN = 'home::%';
	public const STORAGE_OBJECT_USER_PATTERN = 'object::user:%';
	public const STORAGE_LOCAL_PATTERN = 'local::%';
	public const STORAGE_OBJECT_AMAZON_PATTERN = 'object::store:amazon:::%';
	public const STORAGE_FILES_PATH_PATTERN = 'files/%';
	public const STORAGE_VERSIONS_PATH_PATTERN = 'files_versions/%';
	public const STORAGE_TRASHBIN_PATH_PATTERN = 'files_trashbin/%';

	public const MIMETYPE_FOLDER = 2;
}
