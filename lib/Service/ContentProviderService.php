<?php

namespace OCA\Files_Confidential\Service;

use OCA\Files_Confidential\ContentProviders\MicrosoftContentProvider;
use OCA\Files_Confidential\ContentProviders\OpenDocumentContentProvider;
use OCP\Files\File;

class ContentProviderService {
	/**
	 * @var list<\OCA\Files_Confidential\Contract\IContentProvider>
	 */
	private array $providers;

	public function __construct(MicrosoftContentProvider $microsoft, OpenDocumentContentProvider $openDocument) {
		$this->providers[] = $microsoft;
		$this->providers[] = $openDocument;
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
