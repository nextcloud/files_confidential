<?php

namespace OCA\Files_Confidential\Service;

use OCA\Files_Confidential\Contract\IBailsPolicy;
use OCA\Files_Confidential\BailsProviders\MicrosoftOfficeBailsProvider;
use OCA\Files_Confidential\BailsProviders\OpenDocumentBailsProvider;
use OCP\Files\File;

class BailsPolicyProviderService {
	/**
	 * @var list<\OCA\Files_Confidential\Contract\IBailsProvider>
	 */
	private array $providers;

	public function __construct(MicrosoftOfficeBailsProvider $microsoft, OpenDocumentBailsProvider $openDocument) {
		$this->providers[] = $microsoft;
		$this->providers[] = $openDocument;
	}

	public function getPolicyForFile(File $file): ?IBailsPolicy {
		$mimeType = $file->getMimeType();
		foreach ($this->providers as $provider) {
			if (!in_array($mimeType, $provider->getSupportedMimeTypes())) {
				continue;
			}
			return $provider->getPolicyForFile($file);
		}
		return null;
	}
}
