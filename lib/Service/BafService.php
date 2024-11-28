<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

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
			self::ELEMENT_BUSINESS_AUTHORIZATION => function (Reader $reader): array {
				$children = $reader->parseInnerTree();
				if (!is_array($children)) {
					return [];
				}
				/** @var array{name:string, value: array} $child */
				foreach ($children as $child) {
					if ($child['name'] === self::ELEMENT_INCLUDED) {
						return $child['value'];
					}
				}
				return [] ;
			},
			self::ELEMENT_INCLUDED => function (Reader $reader): array {
				$children = $reader->parseInnerTree();
				$categories = [];
				if (!is_array($children)) {
					return $categories;
				}
				$i = 0;
				/** @var array{name: string, attributes: array<string,string>} $child */
				foreach ($children as $child) {
					if ($child['name'] === self::ELEMENT_CATEGORY) {
						$categories[] = new ClassificationLabel(
							$i++,
							'',
							[],
							[$child['attributes']['Identifier']],
							[],
							[$child['attributes']['Name']],
							[],
						);
					}
				}
				return $categories;
			}
		];


		try {
			/** @var IClassificationLabel[] $labels */
			$labels = $service->parse($xml);
			return $labels;
		} catch (ParseException $e) {
			// log
			return [];
		}
	}
}
