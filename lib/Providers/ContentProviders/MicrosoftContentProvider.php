<?php

declare(strict_types=1);

namespace OCA\Files_Confidential\Providers\ContentProviders;

use DOMDocument;
use OCA\Files_Confidential\Contract\IContentProvider;
use OCP\Files\File;
use OCP\Files\InvalidPathException;
use OCP\Files\NotFoundException;
use Sabre\Xml\ParseException;
use Sabre\Xml\Reader;
use Sabre\Xml\Service;

class MicrosoftContentProvider implements IContentProvider {
	public const ELEMENT_HDR = '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}hdr';
	public const ELEMENT_FTR = '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}ftr';
	public const ELEMENT_P = '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}p';
	public const ELEMENT_R = '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}r';
	public const ELEMENT_T = '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}t';
	public const ELEMENT_PICT = '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}pict';
	public const ELEMENT_SHAPE = '{urn:schemas-microsoft-com:vml}shape';
	public const ELEMENT_TEXTPATH = '{urn:schemas-microsoft-com:vml}textpath';

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
	 * @return string
	 */
	public function getContentForFile(File $file): string {
		try {
			if ($file->getSize() === 0) {
				return '';
			}
		} catch (InvalidPathException|NotFoundException $e) {
			return '';
		}

		try {
			$localFilepath = $file->getStorage()->getLocalFile($file->getInternalPath());
		} catch (NotFoundException $e) {
			return '';
		}

		$zipArchive = new \ZipArchive();
		if (!is_string($localFilepath) || $zipArchive->open($localFilepath) === false) {
			return '';
		}

		$xml = $zipArchive->getFromName('word/header1.xml');

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
							foreach ($children as $child) {
								if ($child['name'] === $elementName) {
									if (count($path) - 1 === $i && isset($child['attributes']['string'])) {
										$newChildrenArray[] = $child['attributes']['string'];
										continue;
									}
									if (count($path) - 1 === $i && is_array($child['value'])) {
										continue;
									}
									if (count($path) - 1 !== $i && !is_array($child['value'])) {
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
			$content = implode(' ', $service->parse($xml));
		} catch (ParseException $e) {
			// log
			$content = '';
		}

		$xml = $zipArchive->getFromName('word/footer1.xml');

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
							foreach ($children as $child) {
								if ($child['name'] === $elementName) {
									if (count($path) - 1 === $i && is_array($child['value'])) {
										continue;
									}
									if (count($path) - 1 !== $i && !is_array($child['value'])) {
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
			$content .= implode(' ', $service->parse($xml));
		} catch (ParseException $e) {
			// log
		}

		$data = $zipArchive->getFromName('word/document.xml');

		if ($data !== false) {
			$xml = new DOMDocument();
			$xml->loadXML($data, \LIBXML_NOENT | \LIBXML_XINCLUDE | \LIBXML_NOERROR | \LIBXML_NOWARNING);
			$content .= ' ' . strip_tags($xml->saveXML());
		}

		$zipArchive->close();

		return $content;
	}
}
