<?php

namespace OCA\Files_Confidential\Service;

class MatcherService {
	public $expressions = [
		'E-Mail' => '\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\b',
		'Credit card' => '\b(\d[ -]*?){13,16,19}\b',

		'IBAN' => '\b([A-Z]{2}[ \-]?[0-9]{2})(?=(?:[ \-]?[A-Z0-9]){9,30}$)((?:[ \-]?[A-Z0-9]{3,5}){2,7})([ \-]?[A-Z0-9]{1,3})?\b',

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
		'Personal Identity Number Sweden' => '\b[0-9]{6}-[0-9]{4}\b'
	];

	/**
	 * @param string $name
	 * @return string|null
	 */
	public function getMatchExpression(string $name) : ?string {
		if (!isset($this->expressions[$name])) {
			return null;
		}
		return '/'.$this->expressions[$name].'/';
	}

	private static $instance = null;

	/**
	 * @return static
	 */
	public static function getInstance() : self {
		return self::$instance = (self::$instance ?? new MatcherService());
	}
}
