<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Files_Confidential\Service;

use OCA\Files_Confidential\Providers\ContentProviders\MicrosoftContentProvider;
use OCA\Files_Confidential\Providers\ContentProviders\OpenDocumentContentProvider;
use OCA\Files_Confidential\Providers\ContentProviders\PdfContentProvider;
use OCA\Files_Confidential\Providers\ContentProviders\PlainTextContentProvider;
use OCP\Files\File;

class ContentProviderService {
	/**
	 * @var list<\OCA\Files_Confidential\Contract\IContentProvider>
	 */
	private array $providers = [];

	public function __construct(MicrosoftContentProvider $microsoft, OpenDocumentContentProvider $openDocument, PdfContentProvider $pdf, PlainTextContentProvider $plainText) {
		$this->providers[] = $microsoft;
		$this->providers[] = $openDocument;
		$this->providers[] = $pdf;
		$this->providers[] = $plainText;
	}

	/**
	 * @return \Generator<string>
	 */
	public function getContentStreamForFile(File $file): \Generator {
		$mimeType = $file->getMimeType();
		foreach ($this->providers as $provider) {
			if (!in_array($mimeType, $provider->getSupportedMimeTypes())) {
				continue;
			}
			return $provider->getContentStream($file);
		}
		return (static function (): \Generator { yield ''; })(); // Return an empty generator if no provider matches
	}
}
