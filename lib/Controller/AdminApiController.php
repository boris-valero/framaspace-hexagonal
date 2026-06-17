<?php

declare(strict_types=1);

namespace OCA\FramaSpace\Controller;

use OCA\FramaSpace\Domain\Service\HiddenAppsManager;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

/**
 * @psalm-suppress UnusedClass
 */
class AdminApiController extends Controller {
	public function __construct(
		string $AppName,
		IRequest $request,
		private HiddenAppsManager $hiddenAppsManager,
	) {
		parent::__construct($AppName, $request);
	}

	#[NoCSRFRequired]
	public function setHidden(): DataResponse {
		/** @psalm-suppress MixedAssignment */
		$hiddenParam = $this->request->getParam('hidden', []);
		$hidden = is_array($hiddenParam) ? $hiddenParam : [];

		return new DataResponse(
			$this->hiddenAppsManager->setHidden($hidden),
		);
	}
}
