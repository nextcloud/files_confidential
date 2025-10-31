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

class MicrosoftContentProvider implements IContentProvider {
	public const ELEMENT_RELATIONSHIPS = '{http://schemas.openxmlformats.org/package/2006/relationships}Relationships';
	public const ELEMENT_HDR = '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}hdr';
	public const ELEMENT_FTR = '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}ftr';
	public const ELEMENT_P = '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}p';
	public const ELEMENT_R = '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}r';
	public const ELEMENT_T = '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}t';
	public const ELEMENT_PICT = '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}pict';
	public const ELEMENT_SHAPE = '{urn:schemas-microsoft-com:vml}shape';
	public const ELEMENT_TEXTPATH = '{urn:schemas-microsoft-com:vml}textpath';

	#[\Override]
	public function getSupportedMimeTypes(): array {
		return [
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // docx
			'application/vnd.ms-word.document.macroEnabled.12', // docm
			'application/vnd.openxmlformats-officedocument.wordprocessingml.template', // dotx
			'application/vnd.ms-word.template.macroEnabled.12', // dotm
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

		$xml = $zipArchive->getFromName('word/_rels/document.xml.rels');
		if ($xml === false) {
			// Fallback to empty rels if the file is missing
			$rels = ['headers' => [],'footers' => []];
		} else {
			$service = new Service();
			$service->elementMap = [
				self::ELEMENT_RELATIONSHIPS => function (Reader $reader) {
					$tree = $reader->parseInnerTree();
					$results = ['headers' => [],'footers' => []];

					foreach ((array)$tree as $child) {
						if (!is_array($child) || !isset($child['attributes']['Type'])) {
							continue;
						}
						if ($child['attributes']['Type'] === 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/footer') {
							$results['footers'][] = $child['attributes']['Target'];
						}
						if ($child['attributes']['Type'] === 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/header') {
							$results['headers'][] = $child['attributes']['Target'];
						}
					}

					return $results;
				}
			];

			try {
				/** @var array{headers: list<string>, footers: list<string>} $rels */
				$rels = (array)$service->parse($xml);
				if (!isset($rels['headers'])) {
					$rels['headers'] = [];
				}
				if (!isset($rels['footers'])) {
					$rels['footers'] = [];
				}
			} catch (ParseException $e) {
				// log
				$rels = ['headers' => [],'footers' => []];
			}
		}


		foreach ($rels['headers'] as $target) {
			$xmlContent = $zipArchive->getFromName("word/{$target}");
			if ($xmlContent === false) {
				continue;
			}

			$service = new Service();
			$service->elementMap = [
				self::ELEMENT_HDR => function (Reader $reader) {
					$tree = $reader->parseInnerTree();
					$results = [];

					$paths = [
						[
							self::ELEMENT_P,
							self::ELEMENT_R,
							self::ELEMENT_PICT,
							self::ELEMENT_SHAPE,
							self::ELEMENT_TEXTPATH,
						],
						[
							self::ELEMENT_P,
							self::ELEMENT_R,
							self::ELEMENT_T,
						],
					];

					foreach ($paths as $path) {
						$newChildrenArray = [$tree];
						foreach ($path as $i => $elementName) {
							$childrenArray = $newChildrenArray;
							$newChildrenArray = [];
							foreach ($childrenArray as $children) {
								foreach ((array)$children as $child) {
									if (!is_array($child)) {
										continue;
									}
									if ($child['name'] === $elementName) {
										if (isset($child['attributes']['string']) && count($path) - 1 === $i) {
											$newChildrenArray[] = $child['attributes']['string'];
											continue;
										}
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
				yield implode(' ', (array)$service->parse($xmlContent));
			} catch (ParseException $e) {
				// log
			}
		}


		foreach ($rels['footers'] as $target) {
			$xmlContent = $zipArchive->getFromName("word/{$target}");
			if ($xmlContent === false) {
				continue;
			}

			$service = new Service();
			$service->elementMap = [
				self::ELEMENT_FTR => function (Reader $reader) {
					$tree = $reader->parseInnerTree();
					$results = [];

					$paths = [
						[
							self::ELEMENT_P,
							self::ELEMENT_R,
							self::ELEMENT_T,
						],
					];

					foreach ($paths as $path) {
						$newChildrenArray = [$tree];
						foreach ($path as $i => $elementName) {
							$childrenArray = $newChildrenArray;
							$newChildrenArray = [];
							foreach ($childrenArray as $children) {
								foreach ((array)$children as $child) {
									if (!is_array($child)) {
										continue;
									}
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
				yield implode(' ', (array)$service->parse($xmlContent));
			} catch (ParseException $e) {
				// log
			}
		}

		// Use a streaming XML reader to avoid loading the entire document.xml into memory.
		$uri = 'zip://' . $localFilepath . '#word/document.xml';
		$reader = \XMLReader::open($uri);

		if ($reader === false) {
			$zipArchive->close();
			return;
		}

		$buffer = '';
		$chunkSize = 8192; // Yield content in 8KB chunks

		while ($reader->read()) {
			// We are only interested in the text content, which is inside <w:t> elements.
			if ($reader->nodeType === \XMLReader::ELEMENT && $reader->name === 'w:t') {
				// readString() gets all text until the closing </w:t> tag.
				$buffer .= $reader->readString() . ' ';
			}

			if (strlen($buffer) >= $chunkSize) {
				yield $buffer;
				$buffer = '';
			}
		}

		// Yield any remaining content in the buffer
		if (!empty($buffer)) {
			yield $buffer;
		}

		$reader->close();
		$zipArchive->close();
	}
}
