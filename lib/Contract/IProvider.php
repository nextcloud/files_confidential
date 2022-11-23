<?php

namespace OCA\Files_Confidential\Contract;

use OCP\Files\File;

interface IProvider {
	/**
	 * @return list<string>
	 */
	public function getSupportedMimeTypes(): array;
	public function getPolicyForFile(File $file): ?IPolicy;
}
