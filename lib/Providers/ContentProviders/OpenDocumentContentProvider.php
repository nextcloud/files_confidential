<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Files_Confidential\Providers\ContentProviders;

use OCA\Files_Confidential\Contract\IContentProvider;
use OCP\Files\File;
use OCP\Files\InvalidPathException;
use OCP\Files\NotFoundException;
use Sabre\Xml\ParseException;
use Sabre\Xml\Reader;
use Sabre\Xml\Service;

class OpenDocumentContentProvider implements IContentProvider {
	public const ELEMENT_DOCUMENT_STYLES = '{urn:oasis:names:tc:opendocument:xmlns:office:1.0}document-styles';
	public const ELEMENT_MASTER_STYLES = '{urn:oasis:names:tc:opendocument:xmlns:office:1.0}master-styles';
	public const ELEMENT_MASTER_PAGE = '{urn:oasis:names:tc:opendocument:xmlns:style:1.0}master-page';
	public const ELEMENT_HEADER = '{urn:oasis:names:tc:opendocument:xmlns:style:1.0}header';
	public const ELEMENT_FOOTER = '{urn:oasis:names:tc:opendocument:xmlns:style:1.0}footer';
	public const ELEMENT_TEXT_P = '{urn:oasis:names:tc:opendocument:xmlns:text:1.0}p';
	public const ELEMENT_CUSTOM_SHAPE = '{urn:oasis:names:tc:opendocument:xmlns:drawing:1.0}custom-shape';

	#[\Override]
	public function getSupportedMimeTypes(): array {
		return [
			'application/vnd.oasis.opendocument.text', // odt
			'application/vnd.oasis.opendocument.text-template', // ott
			'application/vnd.oasis.opendocument.text-web', // oth
			'application/vnd.oasis.opendocument.text-master', // odm
		];
	}

	/**
	 * @param \OCP\Files\File $file
	 * @return \Generator<string>
	 */
	#[\Override]
	public function getContentStream(File $file): \Generator {
		try {
			if ($file->getSize() === 0) {
				return;
			}
		} catch (InvalidPathException|NotFoundException $e) {
			return;
		}

		try {
			$localFilepath = $file->getStorage()->getLocalFile($file->getInternalPath());
		} catch (NotFoundException $e) {
			return;
		}

		$zipArchive = new \ZipArchive();
		if (!is_string($localFilepath) || $zipArchive->open($localFilepath) === false) {
			return;
		}

		$xml = $zipArchive->getFromName('styles.xml');

		$service = new Service();
		$service->elementMap = [
			self::ELEMENT_DOCUMENT_STYLES => function (Reader $reader) {
				$tree = $reader->parseInnerTree();
				$results = [];
				$paths = [
					// Watermark
					[
						self::ELEMENT_MASTER_STYLES,
						self::ELEMENT_MASTER_PAGE,
						self::ELEMENT_HEADER,
						self::ELEMENT_TEXT_P,
						self::ELEMENT_CUSTOM_SHAPE,
						self::ELEMENT_TEXT_P
					],
					// Header
					[
						self::ELEMENT_MASTER_STYLES,
						self::ELEMENT_MASTER_PAGE,
						self::ELEMENT_HEADER,
						self::ELEMENT_TEXT_P
					],
					// Footer
					[
						self::ELEMENT_MASTER_STYLES,
						self::ELEMENT_MASTER_PAGE,
						self::ELEMENT_FOOTER,
						self::ELEMENT_TEXT_P
					],
				];
				foreach ($paths as $path) {
					$newChildrenArray = [$tree];
					foreach ($path as $i => $elementName) {
						$childrenArray = $newChildrenArray;
						$newChildrenArray = [];
						foreach ($childrenArray as $children) {
							foreach ($children as $child) {
								if ($child['name'] === $elementName) {
									if (is_array($child['value']) && count($path) - 1 === $i) {
										continue;
									}
									if (!is_array($child['value']) && count($path) - 1 !== $i) {
										continue;
									}
									$newChildrenArray[] = $child['value'];
								}
							}
						}
						if ($i === count($path) - 1 || count($newChildrenArray) === 0) {
							$results = array_merge($results, $newChildrenArray);
							break;
						}
					}
				}
				return $results;
			}
		];

		try {
			$headerFooterContent = (array)$service->parse($xml);
			if (!empty($headerFooterContent)) {
				yield implode(' ', $headerFooterContent);
			}
		} catch (ParseException $e) {
			// log
		}

		$uri = 'zip://' . $localFilepath . '#content.xml';
		$reader = \XMLReader::open($uri);

		if ($reader) {
			$buffer = '';
			$chunkSize = 8192;

			while ($reader->read()) {
				if ($reader->nodeType === \XMLReader::TEXT || $reader->nodeType === \XMLReader::CDATA) {
					$buffer .= $reader->value . ' ';

					if (strlen($buffer) >= $chunkSize) {
						yield $buffer;
						$buffer = '';
					}
				}
			}
			if (!empty($buffer)) {
				yield $buffer;
			}
			$reader->close();
		}

		$zipArchive->close();
	}
}
