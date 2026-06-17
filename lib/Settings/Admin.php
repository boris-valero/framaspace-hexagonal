<?php

declare(strict_types=1);

namespace OCA\FramaSpace\Settings;

use OCA\FramaSpace\AppInfo\Application;
use OCA\FramaSpace\Domain\Port\AppConfigRepository;
use OCA\FramaSpace\Domain\Service\HiddenAppsManager;
use OCP\App\IAppManager;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\INavigationManager;
use OCP\IUserSession;
use OCP\Settings\ISettings;

/**
 * Provides the FramaSpace admin panel
 * @psalm-suppress UnusedClass
 */
class Admin implements ISettings {

	public function __construct(
		private IInitialState $initialState,
		private INavigationManager $navigationManager,
		private IAppManager $appManager,
		private AppConfigRepository $configRepository,
		private HiddenAppsManager $hiddenAppsManager,
		private IUserSession $userSession,
	) {
	}

	public function getForm(): TemplateResponse {
		$navigationEntries = $this->navigationManager->getAll();
		$hiddenApps = $this->configRepository->getHiddenApps();
		/** @var array<array<string, mixed>> $appsData */
		$appsData = [];
		$user = $this->userSession->getUser();
		foreach ($navigationEntries as $entry) {
			if (!is_array($entry) || !isset($entry['id']) || !is_string($entry['id'])) {
				continue;
			}
			$appId = $entry['id'];
			if ($user && $this->appManager->isEnabledForUser($appId, $user)) {
				$appsData[] = [
					'id' => $appId,
					'name' => (string)($entry['name'] ?? $appId),
					'hidden' => in_array($appId, $hiddenApps),
					'protected' => $this->hiddenAppsManager->isProtected($appId),
				];
			}
		}

		$this->initialState->provideInitialState('apps', $appsData);
		return new TemplateResponse(Application::APP_ID, 'settings/admin-form', []);
	}

	public function getSection(): string {
		return Application::APP_ID;
	}

	public function getPriority(): int {
		return 0;
	}
}
