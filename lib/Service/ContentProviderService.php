<?php

declare(strict_types=1);

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

	public function getContentForFile(File $file): string {
		$mimeType = $file->getMimeType();
		foreach ($this->providers as $provider) {
			if (!in_array($mimeType, $provider->getSupportedMimeTypes())) {
				continue;
			}
			return $provider->getContentForFile($file);
		}
		return '';
	}
}
