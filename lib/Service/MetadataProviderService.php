<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Files_Confidential\Service;

use OCA\Files_Confidential\Model\MetadataItem;
use OCA\Files_Confidential\Providers\MetadataProviders\MicrosoftOfficeMetadataProvider;
use OCP\Files\File;

class MetadataProviderService {
	/**
	 * @var list<\OCA\Files_Confidential\Contract\IMetadataProvider>
	 */
	private array $providers = [];

	public function __construct(MicrosoftOfficeMetadataProvider $microsoft) {
		$this->providers[] = $microsoft;
	}

	/**
	 * @param File $file
	 * @return MetadataItem[]
	 */
	public function getMetadataForFile(File $file): array {
		$mimeType = $file->getMimeType();
		foreach ($this->providers as $provider) {
			if (!in_array($mimeType, $provider->getSupportedMimeTypes())) {
				continue;
			}
			return $provider->getMetadataForFile($file);
		}
		return [];
	}
}
