<?php

declare(strict_types=1);

namespace OCA\FramaSpace\Metrics;

class Forms extends BaseMetrics {
	public function countForms(): int {
		return $this->executeCount('forms_v2_forms', 'form_count');
	}

	public function countSubmissions(): int {
		return $this->executeCount('forms_v2_submissions', 'submission_count');
	}

	public function getMetrics(): array {
		return [
			'forms' => $this->countForms(),
			'submissions' => $this->countSubmissions()
		];
	}
}
