<?php

declare(strict_types=1);

namespace OCA\Files_Confidential\Service;

use OCA\Files_Confidential\Providers\BailsProviders\MicrosoftOfficeBailsProvider;
use OCA\Files_Confidential\Providers\BailsProviders\OpenDocumentBailsProvider;
use OCA\Files_Confidential\Contract\IBailsPolicy;
use OCP\Files\File;

class BailsPolicyProviderService {
	/**
	 * @var list<\OCA\Files_Confidential\Contract\IBailsProvider>
	 */
	private array $providers = [];

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
