<?php
/*
 * Copyright (c) 2022 The Recognize contributors.
 * This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\Files_Confidential\Controller;

use OCA\Files_Confidential\Service\SettingsService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IConfig;
use OCP\IRequest;

class AdminController extends Controller {
	private SettingsService $settingsService;


	public function __construct(string $appName, IRequest $request, IConfig $config, SettingsService $settingsService) {
		parent::__construct($appName, $request);
		$this->config = $config;
		$this->settingsService = $settingsService;
	}

	public function setClassificationLabels($value): JSONResponse {
		try {
			$this->settingsService->setClassificationLabels( $value);
			return new JSONResponse([], Http::STATUS_OK);
		} catch (\Exception $e) {
			return new JSONResponse([], Http::STATUS_BAD_REQUEST);
		}
	}
}
