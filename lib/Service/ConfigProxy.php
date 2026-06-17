<?php

declare(strict_types=1);

namespace OCA\FramaSpace\Service;

use OCA\FramaSpace\AppInfo\Application;
use OCP\IAppConfig;

class ConfigProxy {
	/** @psalm-suppress PossiblyUnusedMethod */
	public function __construct(
		protected IAppConfig $config,
	) {
	}

	public function getAppValue(string $name, string $default, ?string $appId = null): string {
		return $this->config->getValueString($appId ?? Application::APP_ID, $name, $default);
	}

	public function setAppValue(string $name, string $value, ?string $appId = null): void {
		$this->config->setValueString($appId ?? Application::APP_ID, $name, $value);
	}

	/**
	 * @psalm-suppress PossiblyUnusedMethod
	 */
	public function getAppValueArray(string $name, string $default = '[]', ?string $appId = null): array {
		try {
			/** @var array<array-key, mixed> $result */
			$result = json_decode($this->getAppValue($name, $default, $appId), true, 512, JSON_THROW_ON_ERROR);
			return $result;
		} catch (\JsonException) {
			return [];
		}
	}

	public function setAppValueArray(string $name, array $value, ?string $appId = null): void {
		$this->setAppValue($name, json_encode($value), $appId);
	}
}
