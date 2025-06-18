<?php

/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Files_Confidential\Contract;

use OCP\Files\File;

interface IContentProvider {
	/**
	 * @return list<string>
	 */
	public function getSupportedMimeTypes(): array;
	/**
	 * @return \Generator<string>
	 */
	public function getContentStream(File $file): \Generator;
}
