<?php

/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Recognize\AppInfo;

return [
	'routes' => [
		// internal ADMIN API
		['name' => 'admin#setClassificationLabels', 'url' => '/admin/settings/labels', 'verb' => 'PUT'],
		['name' => 'admin#getClassificationLabels', 'url' => '/admin/settings/labels', 'verb' => 'GET'],
		['name' => 'admin#importBaf', 'url' => '/admin/baf', 'verb' => 'POST'],
	],
];
