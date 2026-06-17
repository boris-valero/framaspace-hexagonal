<?php

declare(strict_types=1);

namespace OCA\FramaSpace\Settings;

use OCA\FramaSpace\AppInfo\Application;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Settings\IIconSection;

/**
 * @psalm-suppress UnusedClass
 */
class AdminSection implements IIconSection {
	/** @psalm-suppress PossiblyUnusedMethod */
	public function __construct(
		private IURLGenerator $url,
		private IL10N $l,
	) {
	}

	public function getID(): string {
		return Application::APP_ID;
	}

	public function getName(): string {
		return $this->l->t('Framaspace');
	}

	public function getPriority(): int {
		return 50;
	}

	public function getIcon(): string {
		return $this->url->imagePath(Application::APP_ID, 'app.svg');
	}
}
