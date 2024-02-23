<?php

/**
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Stefan Klemm <mail@stefan-klemm.de>
 * @copyright (c) 2014, Stefan Klemm
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
