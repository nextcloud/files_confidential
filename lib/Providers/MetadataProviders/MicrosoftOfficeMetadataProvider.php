<?php

/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Files_Confidential\Providers\MetadataProviders;

use OCA\Files_Confidential\Contract\IMetadataProvider;
use OCA\Files_Confidential\Model\MetadataItem;
use OCP\Files\File;
use OCP\Files\InvalidPathException;
use OCP\Files\NotFoundException;
use Sabre\Xml\ParseException;
use Sabre\Xml\Reader;
use Sabre\Xml\Service;

class MicrosoftOfficeMetadataProvider implements IMetadataProvider {

	public const ELEMENT_PROPERTIES = '{http://schemas.openxmlformats.org/officeDocument/2006/custom-properties}Properties';
	public const ELEMENT_PROPERTY = '{http://schemas.openxmlformats.org/officeDocument/2006/custom-properties}property';
	public const ATTRIBUTE_NAME = 'name';

	#[\Override]
	public function getSupportedMimeTypes(): array {
		return [
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // docx
			'application/vnd.ms-word.document.macroEnabled.12', // docm
			'application/vnd.openxmlformats-officedocument.wordprocessingml.template', // dotx
			'application/vnd.ms-word.template.macroEnabled.12', // dotm
			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // xlsx
			'application/vnd.ms-excel.sheet.macroEnabled.12', // xlsm
			'application/vnd.openxmlformats-officedocument.spreadsheetml.template', // xltx
			'application/vnd.ms-excel.template.macroEnabled.12', // xltm
			'application/vnd.openxmlformats-officedocument.presentationml.presentation', // pptx
			'application/vnd.ms-powerpoint.presentation.macroEnabled.12', // pptm
			'application/vnd.openxmlformats-officedocument.presentationml.template', // potx
			'application/vnd.ms-powerpoint.template.macroEnabled.12', // potm
		];
	}

	/**
	 * @inheritDoc
	 */
	#[\Override]
	public function getMetadataForFile(File $file): array {
		try {
			if ($file->getSize() === 0) {
				return [];
			}
		} catch (InvalidPathException|NotFoundException $e) {
			return [];
		}

		$zipArchive = new \ZipArchive();
		$path = $file->getStorage()->getLocalFile($file->getInternalPath());
		if (!is_string($path) || $zipArchive->open($path) === false) {
			return [];
		}

		$xml = $zipArchive->getFromName('docProps/custom.xml');
		$zipArchive->close();

		$service = new Service();
		$service->elementMap = [
			self::ELEMENT_PROPERTIES => function (Reader $reader): array {
				$children = $reader->parseInnerTree();
				$items = [];
				if (!is_array($children)) {
					return $items;
				}
				/** @var array{name: string, attributes: array<string, string>, value: array} $child */
				foreach ($children as $child) {
					if (
						$child['name'] === self::ELEMENT_PROPERTY
						&& isset($child['attributes'][self::ATTRIBUTE_NAME], $child['value'][0], $child['value'][0]['value'])) {
						$items[] = new MetadataItem($child['attributes'][self::ATTRIBUTE_NAME], $child['value'][0]['value']);
					}
				}
				return $items;
			}
		];


		try {
			/** @var MetadataItem[] $items */
			$items = $service->parse($xml);
		} catch (ParseException $e) {
			// log
			return [];
		}

		return $items;
	}
}
