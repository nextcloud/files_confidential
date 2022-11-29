<?php

namespace OCA\Files_Confidential\StateClassificationProviders;

use OCA\Files_Confidential\Contract\IStateClassificationProvider;
use OCA\Files_Confidential\Model\StateClassification;
use OCP\Files\File;
use OCP\Files\NotFoundException;
use Sabre\Xml\ParseException;
use Sabre\Xml\Reader;
use Sabre\Xml\Service;

class OpenDocumentStateClassificationProvider implements IStateClassificationProvider {
	public const ELEMENT_DOCUMENT_STYLES = '{urn:oasis:names:tc:opendocument:xmlns:office:1.0}document-styles';
	public const ELEMENT_MASTER_STYLES = '{urn:oasis:names:tc:opendocument:xmlns:office:1.0}master-styles';
	public const ELEMENT_MASTER_PAGE = '{urn:oasis:names:tc:opendocument:xmlns:style:1.0}master-page';
	public const ELEMENT_HEADER = '{urn:oasis:names:tc:opendocument:xmlns:style:1.0}header';
	public const ELEMENT_TEXT_P = '{urn:oasis:names:tc:opendocument:xmlns:text:1.0}p';
	public const ELEMENT_CUSTOM_SHAPE = '{urn:oasis:names:tc:opendocument:xmlns:drawing:1.0}custom-shape';

	public function getSupportedMimeTypes(): array {
		return [
			'application/vnd.oasis.opendocument.spreadsheet', // ods
			'application/vnd.oasis.opendocument.text', // odt
			'application/vnd.oasis.opendocument.text-template', // ott
			'application/vnd.oasis.opendocument.text-web', // oth
			'application/vnd.oasis.opendocument.text-master', // odm
			'application/vnd.oasis.opendocument.spreadsheet', // ods
			'application/vnd.oasis.opendocument.spreadsheet-template', // ots
		];
	}

	/**
	 * @param \OCP\Files\File $file
	 * @return int
	 */
	public function getClassificationForFile(File $file): int {
		try {
			$localFilepath = $file->getStorage()->getLocalFile($file->getInternalPath());
		} catch (NotFoundException $e) {
			return 0;
		}

		$zipArchive = new \ZipArchive();
		if ($zipArchive->open($localFilepath) === false) {
			return 0;
		}

		$xml = $zipArchive->getFromName('styles.xml');
		$zipArchive->close();

		$service = new Service();
		$service->elementMap = [
			self::ELEMENT_DOCUMENT_STYLES => function (Reader $reader) {
				$children = $reader->parseInnerTree();
				$path = [
					self::ELEMENT_MASTER_STYLES,
					self::ELEMENT_MASTER_PAGE,
					self::ELEMENT_HEADER,
					self::ELEMENT_TEXT_P,
					self::ELEMENT_CUSTOM_SHAPE,
					self::ELEMENT_TEXT_P
				];
				foreach ($path as $i => $elementName) {
					foreach ($children as $child) {
						if ($i === count($path) - 1) {
							return $child['value'];
						} elseif ($child['name'] === $elementName) {
							$children = $child['value'];
						}
					}
				}
				return '';
			}
		];

		try {
			$watermark = $service->parse($xml);
		} catch (ParseException $e) {
			// log
			return 0;
		}

		return StateClassification::findLabelInText($watermark);
	}
}
