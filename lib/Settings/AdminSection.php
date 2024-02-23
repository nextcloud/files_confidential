<?php

declare(strict_types=1);

/*
 * Copyright (c) 2021-2022 The Recognize contributors.
 * This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\Files_Confidential\Settings;

use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Settings\IIconSection;

class AdminSection implements IIconSection {
	private IURLGenerator $urlGenerator;
	private IL10N $l;

	public function __construct(
		IURLGenerator $urlGenerator,
		IL10N $l
	) {
		$this->urlGenerator = $urlGenerator;
		$this->l = $l;
	}

	/**
	 * @inheritdoc
	 */
	public function getID(): string {
		return 'files_confidential';
	}

	/**
	 * @inheritdoc
	 */
	public function getName(): string {
		return $this->l->t('Confidential Files');
	}

	/**
	 * @inheritdoc
	 */
	public function getIcon(): string {
		return $this->urlGenerator->imagePath('files_confidential', 'files_confidential-dark.svg');
	}

	/**
	 * @inheritdoc
	 */
	public function getPriority(): int {
		return 80;
	}
}
