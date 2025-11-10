<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Files_Confidential\Settings;

use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Settings\IIconSection;

class AdminSection implements IIconSection {
	public function __construct(
		private IURLGenerator $urlGenerator,
		private IL10N $l,
	) {
	}

	/**
	 * @inheritdoc
	 */
	#[\Override]
	public function getID(): string {
		return 'files_confidential';
	}

	/**
	 * @inheritdoc
	 */
	#[\Override]
	public function getName(): string {
		return $this->l->t('Confidential Files');
	}

	/**
	 * @inheritdoc
	 */
	#[\Override]
	public function getIcon(): string {
		return $this->urlGenerator->imagePath('files_confidential', 'files_confidential-dark.svg');
	}

	/**
	 * @inheritdoc
	 */
	#[\Override]
	public function getPriority(): int {
		return 80;
	}
}
