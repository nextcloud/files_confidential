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

class PdfContentProvider implements IContentProvider {
	public function getSupportedMimeTypes(): array {
		return [
			'application/pdf',
			'application/x-pdf',
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

		// Parse PDF file and build necessary objects.
		$parser = new \Smalot\PdfParser\Parser();
		try {
			$pdf = $parser->parseFile($localFilepath);
			$content = $pdf->getText();
		} catch (\Exception $e) {
			return '';
		}

		return $content;
	}
}
