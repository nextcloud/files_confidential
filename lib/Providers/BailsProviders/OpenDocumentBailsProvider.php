<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Files_Confidential\Providers\BailsProviders;

use OCA\Files_Confidential\Contract\IBailsPolicy;
use OCA\Files_Confidential\Contract\IBailsProvider;
use OCA\Files_Confidential\Model\BailsPolicy;
use OCP\Files\File;
use OCP\Files\InvalidPathException;
use OCP\Files\NotFoundException;
use Sabre\Xml\ParseException;
use Sabre\Xml\Reader;
use Sabre\Xml\Service;

class OpenDocumentBailsProvider implements IBailsProvider {
	public const ELEMENT_DOCUMENT_META = '{urn:oasis:names:tc:opendocument:xmlns:office:1.0}document-meta';
	public const ELEMENT_META = '{urn:oasis:names:tc:opendocument:xmlns:office:1.0}meta';
	public const ELEMENT_USER_DEFINED = '{urn:oasis:names:tc:opendocument:xmlns:meta:1.0}user-defined';
	public const ATTRIBUTE_NAME = '{urn:oasis:names:tc:opendocument:xmlns:meta:1.0}name';

	#[\Override]
	public function getSupportedMimeTypes(): array {
		return [
			'application/vnd.oasis.opendocument.presentation', // odp
			'application/vnd.oasis.opendocument.spreadsheet', // ods
			'application/vnd.oasis.opendocument.text', // odt
			'application/vnd.oasis.opendocument.text-template', // ott
			'application/vnd.oasis.opendocument.text-web', // oth
			'application/vnd.oasis.opendocument.text-master', // odm
			'application/vnd.oasis.opendocument.graphics', // odg
			'application/vnd.oasis.opendocument.graphics-template', // otg
			'application/vnd.oasis.opendocument.presentation', // odp
			'application/vnd.oasis.opendocument.presentation-template', // otp
			'application/vnd.oasis.opendocument.spreadsheet', // ods
			'application/vnd.oasis.opendocument.spreadsheet-template', // ots
			'application/vnd.oasis.opendocument.chart', // odc
			'application/vnd.oasis.opendocument.formula', // odf
			'application/vnd.oasis.opendocument.image', // odi
		];
	}

	/**
	 * @param \OCP\Files\File $file
	 * @return \OCA\Files_Confidential\Contract\IBailsPolicy
	 */
	#[\Override]
	public function getPolicyForFile(File $file): ?IBailsPolicy {
		try {
			if ($file->getSize() === 0) {
				return null;
			}
		} catch (InvalidPathException|NotFoundException $e) {
			return null;
		}

		$zipArchive = new \ZipArchive();
		$path = $file->getStorage()->getLocalFile($file->getInternalPath());
		if (!is_string($path) || $zipArchive->open($path) === false) {
			return null;
		}

		$xml = $zipArchive->getFromName('meta.xml');
		$zipArchive->close();

		$service = new Service();
		$service->elementMap = [
			self::ELEMENT_DOCUMENT_META => function (Reader $reader) {
				$children = $reader->parseInnerTree();
				if ($children[0]['name'] !== self::ELEMENT_META) {
					return false;
				}
				return $children[0]['value'];
			},
			self::ELEMENT_META => function (Reader $reader) {
				$children = $reader->parseInnerTree();
				$props = [];
				if (!is_array($children)) {
					return $props;
				}
				foreach ($children as $child) {
					if (
						$child['name'] === self::ELEMENT_USER_DEFINED
						&& isset($child['attributes'][self::ATTRIBUTE_NAME])) {
						$props[] = [
							'key' => $child['attributes'][self::ATTRIBUTE_NAME],
							'value' => $child['value'],
						];
					}
				}
				return $props;
			}
		];

		try {
			/** @var list<array{key: string, value: string}> $props */
			$props = $service->parse($xml);
		} catch (ParseException $e) {
			// log
			return null;
		}

		return BailsPolicy::fromBAILS($props);
	}
}
