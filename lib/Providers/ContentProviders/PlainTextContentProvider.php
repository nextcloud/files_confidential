<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Files_Confidential\Providers\ContentProviders;

use OCA\Files_Confidential\Contract\IContentProvider;
use OCP\Files\File;
use OCP\Files\InvalidPathException;
use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;
use OCP\Lock\LockedException;

class PlainTextContentProvider implements IContentProvider {

	#[\Override]
	public function getSupportedMimeTypes(): array {
		return [
			'text/plain',
			'text/markdown',
			'text/csv',
			'text/html',
		];
	}

	/**
	 * @param File $file
	 *
	 * @return \Generator<int, string, false|mixed, void>
	 */
	#[\Override]
	public function getContentStream(File $file): \Generator {
		try {
			if ($file->getSize() === 0) {
				return;
			}
		} catch (InvalidPathException|NotFoundException $e) {
			return;
		}

		try {
			$stream = $file->fopen('r');
			if ($stream) {
				while (!feof($stream)) {
					yield fread($stream, 8192);
				}
				fclose($stream);
			}
		} catch (NotPermittedException|LockedException $e) {
			// ignore and return empty generator
			yield '';
		}
	}
}
