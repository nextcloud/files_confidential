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
use Smalot\PdfParser\Config;

class PdfContentProvider implements IContentProvider {
	#[\Override]
	public function getSupportedMimeTypes(): array {
		return [
			'application/pdf',
			'application/x-pdf',
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

		if (!$localFilepath) {
			return;
		}

		// Parse PDF file and build necessary objects.
		$config = new Config();
		// Disable image content retention to save memory and avoid decompression bombs, we don't need it
		$config->setRetainImageContent(false);
		// Set memory limit to 50MB to avoid decompression bombs
		$config->setDecodeMemoryLimit(50 * 1024 * 1024);
		$parser = new \Smalot\PdfParser\Parser([], $config);
		try {
			$pdf = $parser->parseFile($localFilepath);
			foreach ($pdf->getPages() as $page) {
				yield $page->getText();
			}
		} catch (\Exception $e) {
			// ignore and return empty generator
			yield '';
		}
	}
}
