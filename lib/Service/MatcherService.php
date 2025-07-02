<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Files_Confidential\Service;

class MatcherService {
	/**
	 * @var string[]
	 */
	public array $expressions = [
		'E-Mail' => '\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}\b',
		'Credit card' => '\b(\d[ -]*?){13,16,19}\b',

		'IBAN' => '\b([A-Z]{2}[ \-]?[0-9]{2})(?=(?:[ \-]?[A-Z0-9]){9,30}\b)((?:[ \-]?[A-Z0-9]{3,5}){2,7})([ \-]?[A-Z0-9]{1,3})?\b',

		// National Identity Number
		'Nigeria National Identity Number' => '\b[0-9]{11}\b',
		'South Africa National Identity Number' => '\b[0-9]{13}\b',
		'Canadian Social insurance number' => '\b[0-9]{3}-[0-9]{3}-[0-9]{3}\b',
		'Rol Único Nacional Chile' => '\b[0-9]{1,2}\.[0-9]{3}\.[0-9]{3}-[0-9A-Z]\b',
		'US Social Security Number' => '\b(\d[ -]*?){9}\b',
		'India Aadhaar' => '\b\d{12}\b',
		'China Identity Card Number' => '\b\d{18}\b',
		'Hong Kong Identity Card Number' => '\b[A-Z]{2}[0-9]{6}[0-9A]\b',
		'Belgium National Register Number' => '\b(\d[ -]*?){9}\b',
		'Danish CPR' => '\b[0-9]{6}-[0-9]{4}\b',
		'Estonian Personal Identification Code' => '\b[0-9]{11}\b',
		'French INSEE' => '\b[0-9]{13}\s?[0-9]{2}\b',
		'National identity number (Norway)' => '\b[0-9]{11}\b',
		'National Identity Number Spain' => '\b[0-9]{8}[A-Z]\b',
		'Personal Identity Number Sweden' => '\b[0-9]{6}-[0-9]{4}\b',
		'National Registration Identity Card Number Singapore' => '\b[stfgmSTFGM]\d{7}\b',
		'National Registration Identity Card Number Malaysia (myKad)' => '\b\d{6}-\d{2}-\d{4}\b',

		// Driver's License numbers
		'United Kingdom Driver\'s License' => '\b[A-Za-z0-9][A-Za-z0-9]{4}\d[0156](0[1-9]|[12]\d|3[01])\d{2}[A-Za-z0-9]{3}[A-Za-z]{2}\d{2}\D\b',
		'Finland Driver\'s License' => '\b[A-Za-z0-9][A-Za-z0-9]{9}[^A-Za-z0-9]\b',
		'Portugal Driver\'s License' => '\b[A-Za-z0-9][A-Za-z0-9]{7,9}[^A-Za-z0-9]\b',
		'Spain Driver\'s License' => '\b[A-Za-z0-9][A-Za-z0-9]{8}[^A-Za-z0-9]\b',
		'France Driver\'s License' => '\b\d{12}\D\b',
		'Japan Driver\'s License' => '\b\d{12}\D\b',
		'Belgium Driver\'s License' => '\b\d{10}\D\b',
		'Netherlands Driver\'s License' => '\b\d{10}\D\b',
		'Sweden Driver\'s License' => '\b\d{10}\D\b',
		'Taiwan ID Number' => '\b[a-zA-Z][12]\d{8}\b',
		'Philippines Driver\'s License' => '\b[a-zA-Z]\d{2}[-]?\d{2}[-]?\d{6}\b',
	];

	/**
	 * @param string $name
	 * @return string|null
	 */
	public function getMatchExpression(string $name) : ?string {
		if (!isset($this->expressions[$name])) {
			return null;
		}
		return '/' . $this->expressions[$name] . '/';
	}

	private static ?MatcherService $instance = null;

	/**
	 * @return MatcherService
	 */
	public static function getInstance() : MatcherService {
		return self::$instance = (self::$instance ?? new MatcherService());
	}
}
