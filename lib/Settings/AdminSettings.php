<?php

declare(strict_types=1);

/*
 * Copyright (c) 2021-2022 The Recognize contributors.
 * This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\Files_Confidential\Settings;

use OCA\Files_Confidential\Service\MatcherService;
use OCA\Files_Confidential\Service\SettingsService;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IInitialStateService;
use OCP\Settings\ISettings;

class AdminSettings implements ISettings {
	public function __construct(
		private SettingsService $settingsService,
		private IInitialStateService $initialState,
		private MatcherService $matcherService
	) {
	}

	/**
	 * @return TemplateResponse
	 */
	public function getForm(): TemplateResponse {
		$labels = $this->settingsService->getClassificationLabels();
		$labels = array_map(fn ($label) => $label->toArray(), $labels);
		$this->initialState->provideInitialState('files_confidential', 'labels', $labels);
		$this->initialState->provideInitialState('files_confidential', 'searchExpressions', $this->matcherService->expressions);

		return new TemplateResponse('files_confidential', 'admin');
	}

	/**
	 * @inheritdoc
	 */
	public function getSection(): string {
		return 'files_confidential';
	}

	/**
	 * @inheritdoc
	 */
	public function getPriority(): int {
		return 1;
	}
}
