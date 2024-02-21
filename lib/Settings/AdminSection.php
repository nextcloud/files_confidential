<?php
/*
 * Copyright (c) 2021-2022 The Recognize contributors.
 * This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\Files_Confidential\Settings;

use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Settings\IIconSection;

class AdminSection implements IIconSection {
	/*private IL10N $l;*/
	private IURLGenerator $urlgen;

	public function __construct(/*IL10N $l, */IURLGenerator $urlgen) {
		//$this->l = $l;
		$this->urlgen = $urlgen;
	}

	/**
	 * returns the ID of the section. It is supposed to be a lower case string
	 *
	 *
	 * @return string
	 */
	public function getID(): string {
		return 'files_confidential';
	}

	/**
	 * returns the translated name as it should be displayed, e.g. 'LDAP / AD
	 * integration'. Use the L10N service to translate it.
	 *
	 * @return string
	 */
	public function getName(): string {
		return 'Confidential Files';
	}

	/**
	 * @return string
	 */
	public function getIcon(): string {
		return $this->urlgen->imagePath('files_confidential', 'files_confidential-dark.svg');
	}

	/**
	 * @return int whether the form should be rather on the top or bottom of the settings navigation. The sections are arranged in ascending order of the priority values. It is required to return a value between 0 and 99.
	 */
	public function getPriority(): int {
		return 80;
	}
}
