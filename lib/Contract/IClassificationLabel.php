<?php

/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Files_Confidential\Contract;

use OCA\Files_Confidential\Model\MetadataItem;

interface IClassificationLabel {
	/**
	 * The lower the index the more important the label
	 * @return int
	 */
	public function getIndex(): int;
	public function getTag(): string;

	/**
	 * @return list<string>
	 */
	public function getKeywords(): array;

	/**
	 * @return list<string>
	 */
	public function getBailsCategories(): array;

	/**
	 * @return list<string>
	 */
	public function getSearchExpressions(): array;

	/**
	 * @return list<string>
	 */
	public function getRegularExpressions(): array;

	/**
	 * @return MetadataItem[]
	 */
	public function getMetadataItems(): array;

	/**
	 * @return array
	 */
	public function toArray(): array;

	/**
	 * @return int
	 */
	public function getMaxMatchLength(): int;
}
