<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Files_Confidential\Model;

use OCA\Files_Confidential\Contract\IClassificationLabel;
use OCA\Files_Confidential\Service\MatcherService;

class ClassificationLabel implements IClassificationLabel {
	private string $tag;
	private int $index;
	/**
	 * @var list<string>
	 */
	private array $keywords;
	/**
	 * @var list<string>
	 */
	private array $categories;

	/**
	 * @var list<string>
	 */
	private array $searchExpressions;

	/**
	 * @var list<string>
	 */
	private array $regularExpressions;

	/**
	 * @var MetadataItem[]
	 */
	private array $metadataItems;

	/**
	 * @param string $text
	 * @param list<IClassificationLabel> $labels
	 * @return IClassificationLabel|null
	 */
	public static function findLabelsInText(string $text, array $labels): ?IClassificationLabel {
		$matcherService = MatcherService::getInstance();
		foreach ($labels as $label) {
			foreach ($label->getKeywords() as $keyword) {
				if (stripos($text, $keyword) !== false) {
					return $label;
				}
			}
			foreach ($label->getSearchExpressions() as $expression) {
				$pattern = $matcherService->getMatchExpression($expression);
				if ($pattern !== null && $pattern !== '' && preg_match($pattern, $text) === 1) {
					return $label;
				}
			}
			foreach ($label->getRegularExpressions() as $pattern) {
				if (preg_match('/' . $pattern . '/', $text) === 1) {
					return $label;
				}
			}
		}
		return null;
	}

	/**
	 * @param MetadataItem[] $metadataItems
	 * @param list<IClassificationLabel> $labels
	 * @return IClassificationLabel|null
	 */
	public static function findLabelsInMetadata(array $metadataItems, array $labels): ?IClassificationLabel {
		foreach ($labels as $label) {
			$matchedKeys = 0;
			foreach ($label->getMetadataItems() as $labelMetadataItem) {
				foreach ($metadataItems as $fileMetadataItem) {
					if ($labelMetadataItem->getKey() === $fileMetadataItem->getKey()) {
						$matchedKeys++;
						if ($labelMetadataItem->getValue() !== $fileMetadataItem->getValue()) {
							continue 3; // go to next label
						}
					}
				}
			}
			if (count($label->getMetadataItems()) === $matchedKeys && $matchedKeys > 0) {
				return $label;
			}
		}
		return null;
	}

	/**
	 * @param int $index
	 * @param string $tag
	 * @param list<string> $keywords
	 * @param list<string> $categories
	 * @param list<string> $searchExpressions
	 * @param list<string> $regularExpressions
	 * @param MetadataItem[] $metadataItems
	 */
	public function __construct(int $index, string $tag, array $keywords, array $categories, array $searchExpressions, array $regularExpressions, array $metadataItems) {
		$this->index = $index;
		$this->tag = $tag;
		$this->keywords = $keywords;
		$this->categories = $categories;
		$this->searchExpressions = $searchExpressions;
		$this->regularExpressions = $regularExpressions;
		$this->metadataItems = $metadataItems;
	}

	/**
	 * @param array{index:int, tag:string, keywords:list<string>, categories:list<string>, searchExpressions:list<string>, regularExpressions:list<string>, metadataItems: list<array{key: string, value: string}>} $labelRaw
	 *
	 * @throws \ValueError
	 */
	public static function fromArray(array $labelRaw): self {
		if (!isset($labelRaw['index'], $labelRaw['tag'], $labelRaw['keywords'], $labelRaw['categories'], $labelRaw['searchExpressions'], $labelRaw['regularExpressions'])) {
			throw new \ValueError();
		}
		$metadata = array_values(array_filter(array_map(fn ($item) => MetadataItem::fromArray($item), $labelRaw['metadataItems'] ?? []), fn ($item) => $item->getKey() !== ''));
		return new ClassificationLabel($labelRaw['index'], $labelRaw['tag'], $labelRaw['keywords'], $labelRaw['categories'], $labelRaw['searchExpressions'], $labelRaw['regularExpressions'], $metadata);
	}

	#[\Override]
	public function toArray() : array {
		return [
			'index' => $this->getIndex(),
			'tag' => $this->getTag(),
			'keywords' => $this->getKeywords(),
			'categories' => $this->getBailsCategories(),
			'searchExpressions' => $this->getSearchExpressions(),
			'regularExpressions' => $this->getRegularExpressions(),
			'metadataItems' => array_map(fn ($item) => $item->toArray(), $this->getMetadataItems()),
		];
	}

	#[\Override]
	public function getIndex(): int {
		return $this->index;
	}

	#[\Override]
	public function getTag(): string {
		return $this->tag;
	}

	#[\Override]
	public function getKeywords(): array {
		return $this->keywords;
	}

	#[\Override]
	public function getBailsCategories(): array {
		return $this->categories;
	}

	#[\Override]
	public function getSearchExpressions(): array {
		return $this->searchExpressions;
	}

	#[\Override]
	public function getRegularExpressions(): array {
		return $this->regularExpressions;
	}

	#[\Override]
	public function getMetadataItems(): array {
		return $this->metadataItems;
	}

	#[\Override]
	public function getMaxMatchLength(): int {
		$maxLength = 0;

		$keywords = $this->getKeywords();
		if (count($keywords) > 0) {
			$maxLength = max(array_map('strlen', $keywords));
		}

		$searchExpressions = $this->getSearchExpressions();
		if (count($searchExpressions) > 0) {
			$maxLength = max(array_map('strlen', $searchExpressions));
		}

		$regularExpressions = $this->getRegularExpressions();
		if (count($regularExpressions) > 0) {
			$maxLength = max(array_map('strlen', $regularExpressions));
		}

		if (count($regularExpressions) > 0 || count($searchExpressions) > 0) {
			$maxLength = max($maxLength, 256);
		}

		return $maxLength;
	}
}
