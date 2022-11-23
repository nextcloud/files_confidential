<?php

namespace OCA\Files_Confidential\Providers;

use OCA\Files_Confidential\Contract\IPolicy;
use OCA\Files_Confidential\Contract\IProvider;
use OCA\Files_Confidential\Model\Policy;
use OCP\Files\File;
use Sabre\Xml\ParseException;
use Sabre\Xml\Reader;
use Sabre\Xml\Service;

class MicrosoftOfficeProvider implements IProvider {
	public const ELEMENT_PROPERTIES = '{http://schemas.openxmlformats.org/officeDocument/2006/custom-properties}Properties';
	public const ELEMENT_PROPERTY = '{http://schemas.openxmlformats.org/officeDocument/2006/custom-properties}property';
	public const ATTRIBUTE_NAME = '{http://schemas.openxmlformats.org/officeDocument/2006/custom-properties}name';// not sure if the ns is necessary here

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
	 * @param \OCP\Files\File $file
	 * @return \OCA\Files_Confidential\Contract\IPolicy
	 */
	public function getPolicyForFile(File $file): ?IPolicy {
		$zipArchive = new \ZipArchive();
		if ($zipArchive->open($file->getStorage()->getLocalFile($file->getInternalPath())) === false) {
			return null;
		}

		$xml = $zipArchive->getFromName('docProps/custom.xml');
		$zipArchive->close();

		$service = new Service();
		$service->elementMap = [
			self::ELEMENT_PROPERTIES => function (Reader $reader) {
				$children = $reader->parseInnerTree();
				$props = [];
				foreach ($children as $child) {
					if (
						$child['name'] === self::ELEMENT_PROPERTY &&
						isset($child['attributes'][self::ATTRIBUTE_NAME], $child['value'][0], $child['value'][0]['value'])) {
						$props[] = [
							'key' => $child['attributes'][self::ATTRIBUTE_NAME],
							'value' => $child['value'][0]['value'],
						];
					}
				}
				return $props;
			}
		];

		try {
			$props = $service->parse($xml);
		} catch (ParseException $e) {
			// log
			return null;
		}

		return Policy::fromBAILS($props);
	}
}
