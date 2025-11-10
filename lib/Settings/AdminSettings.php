<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Files_Confidential\Settings;

use OCA\Files_Confidential\Service\MatcherService;
use OCA\Files_Confidential\Service\SettingsService;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\Settings\ISettings;

class AdminSettings implements ISettings {
	public function __construct(
		private SettingsService $settingsService,
		private IInitialState $initialState,
		private MatcherService $matcherService,
	) {
	}

	/**
	 * @return TemplateResponse
	 */
	#[\Override]
	public function getForm(): TemplateResponse {
		$labels = $this->settingsService->getClassificationLabels();
		$labels = array_map(fn ($label) => $label->toArray(), $labels);
		$this->initialState->provideInitialState('labels', $labels);
		$this->initialState->provideInitialState('searchExpressions', $this->matcherService->expressions);

		return new TemplateResponse('files_confidential', 'admin');
	}

	/**
	 * @inheritdoc
	 */
	#[\Override]
	public function getSection(): string {
		return 'files_confidential';
	}

	/**
	 * @inheritdoc
	 */
	#[\Override]
	public function getPriority(): int {
		return 1;
	}
}
