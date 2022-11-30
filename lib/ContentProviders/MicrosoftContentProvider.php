<?php

namespace OCA\Files_Confidential\ContentProviders;

use OCA\Files_Confidential\Contract\IContentProvider;
use OCP\Files\File;
use OCP\Files\NotFoundException;
use Sabre\Xml\ParseException;
use Sabre\Xml\Reader;
use Sabre\Xml\Service;

class MicrosoftContentProvider implements IContentProvider {
	public const ELEMENT_HDR = '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}hdr';
	public const ELEMENT_P = '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}p';
	public const ELEMENT_R = '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}r';
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
			$localFilepath = $file->getStorage()->getLocalFile($file->getInternalPath());
		} catch (NotFoundException $e) {
			return 0;
		}

		$zipArchive = new \ZipArchive();
		if ($zipArchive->open($localFilepath) === false) {
			return 0;
		}

		$xml = $zipArchive->getFromName('word/header1.xml');
		$zipArchive->close();

		$service = new Service();
		$service->elementMap = [
			self::ELEMENT_HDR => function (Reader $reader) {
				$children = $reader->parseInnerTree();
				$path = [
					self::ELEMENT_P,
					self::ELEMENT_R,
					self::ELEMENT_PICT,
					self::ELEMENT_SHAPE,
					self::ELEMENT_TEXTPATH,
				];
				foreach ($path as $i => $elementName) {
					foreach ($children as $child) {
						if ($i === count($path) - 1 && $child['name'] === $elementName) {
							return $child['attributes']['string'];
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
			return '';
		}

		return $watermark;
	}
}
