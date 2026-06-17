<?php

declare(strict_types=1);

namespace OCA\FramaSpace\Infrastructure\Config;

use OCA\FramaSpace\AppInfo\Application;
use OCA\FramaSpace\Domain\Port\AppConfigRepository;
use OCP\IAppConfig;

class NextcloudAppConfigRepository implements AppConfigRepository {
	public function __construct(
		private IAppConfig $config,
	) {
	}

	public function getHiddenApps(): array {
		try {
			$value = $this->config->getValueString(Application::APP_ID, 'hidden_apps', '[]');
			/** @var array<array-key, mixed> $result */
			$result = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
			return $result;
		} catch (\JsonException) {
			return [];
		}
	}

	public function setHiddenApps(array $hiddenApps): void {
		$this->config->setValueString(Application::APP_ID, 'hidden_apps', json_encode($hiddenApps));
	}
}
