<?php

declare(strict_types=1);

namespace OCA\FramaSpace\Domain\Port;

interface AppConfigRepository {
	public function getHiddenApps(): array;

	public function setHiddenApps(array $hiddenApps): void;
}
