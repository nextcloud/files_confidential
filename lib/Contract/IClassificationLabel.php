<?php

namespace OCA\Files_Confidential\Contract;

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
}
