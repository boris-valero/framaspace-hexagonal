<?php

declare(strict_types=1);

namespace OCA\FramaSpace\Domain\Service;

use OCA\FramaSpace\Domain\Port\AppConfigRepository;

class HiddenAppsManager {
	private const PROTECTED_APPS = ['files', 'activity'];

	public function __construct(
		private AppConfigRepository $config,
	) {
	}

	public function setHidden(array $hiddenParam): array {
		$validatedApps = array_values(array_filter($hiddenParam, 'is_string'));

		$filteredApps = array_diff($validatedApps, self::PROTECTED_APPS);
		$ignoredProtected = array_intersect($validatedApps, self::PROTECTED_APPS);

		$this->config->setHiddenApps($filteredApps);

		return [
			'success' => true,
			'hidden_apps' => $filteredApps,
			'ignored_protected_apps' => array_values($ignoredProtected),
		];
	}

	public function getHiddenApps(): array {
		return $this->config->getHiddenApps();
	}

	public function isProtected(string $appId): bool {
		return in_array($appId, self::PROTECTED_APPS, true);
	}
}
