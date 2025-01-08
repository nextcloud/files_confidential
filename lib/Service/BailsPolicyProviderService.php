<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Files_Confidential\Service;

use OCA\Files_Confidential\Contract\IBailsPolicy;
use OCA\Files_Confidential\Providers\BailsProviders\MicrosoftOfficeBailsProvider;
use OCA\Files_Confidential\Providers\BailsProviders\OpenDocumentBailsProvider;
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
			try {
				return $provider->getPolicyForFile($file);
			} catch (\Throwable) {
				return null;
			}
		}
		return null;
	}
}
