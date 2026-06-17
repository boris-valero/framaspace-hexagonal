<?php

return [
	'ocs' => [
		[
			'name' => 'stats#getStats',
			'url' => '/api/v1/stats',
			'verb' => 'GET',
		],
	],
	'resources' => [],
	'routes' => [
		[
			'name' => 'adminApi#setHidden',
			'url' => '/api/v1/admin/hidden',
			'verb' => 'POST'
		],
	]
];
