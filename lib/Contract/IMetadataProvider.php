<?php

/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Files_Confidential\Contract;

use OCA\Files_Confidential\Model\MetadataItem;
use OCP\Files\File;

interface IMetadataProvider {
	/**
	 * @return list<string>
	 */
	public function getSupportedMimeTypes(): array;

	/**
	 * @param File $file
	 * @return MetadataItem[]
	 */
	public function getMetadataForFile(File $file): array;
}
