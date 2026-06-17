<?php

declare(strict_types=1);

namespace OCA\FramaSpace\Listener;

use OCA\FramaSpace\Domain\Port\AppConfigRepository;
use OCP\AppFramework\Http\Events\BeforeTemplateRenderedEvent;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Util;

/**
 * @implements IEventListener<BeforeTemplateRenderedEvent>
 */
class CSSInjectionListener implements IEventListener {

	/**
	 * @psalm-suppress PossiblyUnusedMethod
	 */
	public function __construct(
		private AppConfigRepository $configRepository,
	) {
	}

	public function handle(Event $event): void {
		if (!($event instanceof BeforeTemplateRenderedEvent)) {
			return;
		}

		$hiddenApps = $this->configRepository->getHiddenApps();
		$hiddenApps = array_filter($hiddenApps, fn ($appId) => is_string($appId) && !empty($appId));

		if (empty($hiddenApps)) {
			return;
		}

		$this->injectHiddenAppsCSS($hiddenApps);
	}

	private function injectHiddenAppsCSS(array $hiddenApps): void {
		$css = $this->generateHiddenAppsCSS($hiddenApps);
		Util::addHeader('style', ['id' => 'framaspace-hidden-apps'], $css);
	}

	private function generateHiddenAppsCSS(array $hiddenApps): string {
		$css = '';

		/** @var string $appId */
		foreach ($hiddenApps as $appId) {
			// Use CSS escaped slashes to avoid quote escaping in injected style headers.
			$css .= ".app-menu-entry:has(a.app-menu-entry__link[href$=\\2f apps\\2f {$appId}\\2f ]) { display: none; }";
			$css .= ".app-menu__overflow-entry:has(a[href$=\\2f apps\\2f {$appId}\\2f ]) { display: none; }";
		}

		return $css;
	}
}
