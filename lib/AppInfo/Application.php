<?php

declare(strict_types=1);

namespace OCA\FramaSpace\AppInfo;

use OCA\FramaSpace\Domain\Port\AppConfigRepository;
use OCA\FramaSpace\Infrastructure\Config\NextcloudAppConfigRepository;
use OCA\FramaSpace\Listener\CSSInjectionListener;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Http\Events\BeforeTemplateRenderedEvent;


class Application extends App implements IBootstrap {
	public const APP_ID = 'framaspace';
	/**
	 * @psalm-suppress PossiblyUnusedMethod
	 */
	public function __construct() {
		parent::__construct(self::APP_ID);
	}

	public function register(IRegistrationContext $context): void {
		$context->registerEventListener(BeforeTemplateRenderedEvent::class, CSSInjectionListener::class);

		$context->registerService(AppConfigRepository::class, function ($c) {
			return $c->get(NextcloudAppConfigRepository::class);
		});
	}

	public function boot(IBootContext $context): void {
	}
}
