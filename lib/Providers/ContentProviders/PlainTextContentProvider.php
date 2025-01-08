<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Files_Confidential\Providers\ContentProviders;

use OCA\Files_Confidential\Contract\IContentProvider;
use OCP\Files\File;
use OCP\Files\GenericFileException;
use OCP\Files\InvalidPathException;
use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;
use OCP\Lock\LockedException;

class PlainTextContentProvider implements IContentProvider {

	public function getSupportedMimeTypes(): array {
		return [
			'text/plain',
			'text/markdown',
			'text/csv',
			'text/html',
		];
	}

	/**
	 * @param \OCP\Files\File $file
	 * @return string
	 */
	public function getContentForFile(File $file): string {
		try {
			if ($file->getSize() === 0) {
				return '';
			}
		} catch (InvalidPathException|NotFoundException $e) {
			return '';
		}

		try {
			return $file->getContent();
		} catch (GenericFileException|NotPermittedException|LockedException $e) {
			return '';
		}
	}
}
