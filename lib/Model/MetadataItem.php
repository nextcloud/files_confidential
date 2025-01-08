<?php

/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Files_Confidential\Model;

class MetadataItem {

	/**
	 * @param string $key
	 * @param string $value
	 */
	public function __construct(
		private string $key,
		private string $value,
	) {
	}


	public function getKey(): string {
		return $this->key;
	}

	public function setKey(string $key): MetadataItem {
		$this->key = $key;
		return $this;
	}

	public function getValue(): string {
		return $this->value;
	}

	public function setValue(string $value): MetadataItem {
		$this->value = $value;
		return $this;
	}

	/**
	 * @return array{key: string, value: string}
	 */
	public function toArray(): array {
		return [
			'key' => $this->key,
			'value' => $this->value,
		];
	}

	/**
	 * @param array $metadataItem
	 * @return MetadataItem
	 */
	public static function fromArray(array $metadataItem): MetadataItem {
		return new MetadataItem($metadataItem['key'] ?? '', $metadataItem['value'] ?? '');
	}
}
