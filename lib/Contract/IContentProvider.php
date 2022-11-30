<?php

namespace OCA\Files_Confidential\Contract;

use OCP\Files\File;

interface IContentProvider {
	/**
	 * @return list<string>
	 */
	public function getSupportedMimeTypes(): array;
	public function getContentForFile(File $file): string;
}
