<?php

declare(strict_types=1);

namespace OCA\FramaSpace\Controller;

use OCA\FramaSpace\Domain\Service\MetricsAggregator;
use OCP\AppFramework\Http\Attribute\ApiRoute;
use OCP\AppFramework\Http\Attribute\CORS;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\IRequest;

class StatsController extends OCSController {
	public function __construct(
		string $appName,
		IRequest $request,
		private MetricsAggregator $metricsAggregator,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * Get statistics for all apps
	 *
	 * @return DataResponse<200, array, array{}>
	 */
	#[NoCSRFRequired]
	#[CORS]
	#[ApiRoute(verb: 'GET', url: '/api/v1/stats')]
	public function getStats(): DataResponse {
		return new DataResponse($this->metricsAggregator->getAll());
	}
}
