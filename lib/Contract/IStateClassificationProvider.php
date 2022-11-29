<?php

namespace OCA\Files_Confidential\Contract;

use OCP\Files\File;

interface IStateClassificationProvider {
	/**
	 * @return list<string>
	 */
	public function getSupportedMimeTypes(): array;
	public function getClassificationForFile(File $file): int;
}
