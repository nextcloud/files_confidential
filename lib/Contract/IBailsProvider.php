<?php

/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Files_Confidential\Contract;

use OCP\Files\File;

interface IBailsProvider {
	/**
	 * @return list<string>
	 */
	public function getSupportedMimeTypes(): array;
	public function getPolicyForFile(File $file): ?IBailsPolicy;
}
