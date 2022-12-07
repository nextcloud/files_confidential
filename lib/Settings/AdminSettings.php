<?php
/*
 * Copyright (c) 2021-2022 The Recognize contributors.
 * This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\Files_Confidential\Settings;

use OCA\Files_Confidential\Service\SettingsService;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IInitialStateService;
use OCP\Settings\ISettings;

class AdminSettings implements ISettings {
	private SettingsService $settingsService;
	private IInitialStateService $initialState;

	public function __construct(SettingsService $settingsService, IInitialStateService $initialState) {
		$this->settingsService = $settingsService;
		$this->initialState = $initialState;
	}

	/**
	 * @return TemplateResponse
	 */
	public function getForm(): TemplateResponse {
		$labels = $this->settingsService->getClassificationLabels();
		$labels = array_map(fn ($label) => $label->toArray(), $labels);
		$this->initialState->provideInitialState('files_confidential', 'labels', $labels);

		return new TemplateResponse('files_confidential', 'admin');
	}

	/**
	 * @return string the section ID, e.g. 'sharing'
	 */
	public function getSection(): string {
		return 'files_confidential';
	}

	/**
	 * @return int whether the form should be rather on the top or bottom of the admin section. The forms are arranged in ascending order of the priority values. It is required to return a value between 0 and 100.
	 */
	public function getPriority(): int {
		return 1;
	}
}
