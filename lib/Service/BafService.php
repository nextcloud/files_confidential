<?php

namespace OCA\Files_Confidential\Service;

use OCA\Files_Confidential\Contract\IClassificationLabel;
use OCA\Files_Confidential\Model\ClassificationLabel;
use Sabre\Xml\ParseException;
use Sabre\Xml\Reader;
use Sabre\Xml\Service;

class BafService {
	public const ELEMENT_BUSINESS_AUTHORIZATION = '{urn:tscp:names:baf:1.1}BusinessAuthorization';
	public const ELEMENT_INCLUDED = '{urn:tscp:names:baf:1.1}Included';
	public const ELEMENT_CATEGORY = '{urn:tscp:names:baf:1.1}BusinessAuthorizationCategory';

	/**
	 * @param string $xml
	 * @return IClassificationLabel[]
	 */
	public function parseXml(string $xml) : array {
		$service = new Service();
		$service->elementMap = [
			self::ELEMENT_BUSINESS_AUTHORIZATION => function (Reader $reader) {
				$children = $reader->parseInnerTree();
				foreach ($children as $child) {
					if ($child['name'] === self::ELEMENT_INCLUDED) {
						return $child['value'];
					}
				}
				return [] ;
			},
			self::ELEMENT_INCLUDED => function (Reader $reader) {
				$children = $reader->parseInnerTree();
				$categories = [];
				$i = 0;
				foreach ($children as $child) {
					if ($child['name'] === self::ELEMENT_CATEGORY) {
						$categories[] = new ClassificationLabel(
							$i++,
							$child['attributes']['Name'],
							[$child['attributes']['Name']],
							[$child['attributes']['Identifier']],
							[],
						);
					}
				}
				return $categories;
			}
		];


		try {
			return $service->parse($xml);
		} catch (ParseException $e) {
			// log
			return [];
		}
	}
}
