<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Files_Confidential\Service;

use OCA\Files_Confidential\Contract\IClassificationLabel;
use OCA\Files_Confidential\Model\MetadataItem;
use OCP\Files\File;

class ClassificationService {
	public function __construct(
		private ContentProviderService $contentService,
		private MetadataProviderService $metadataService,
		private BailsPolicyProviderService $bailsService,
		private SettingsService $settings,
		private MatcherService $matcherService,
	) {
	}

	/**
	 * Get the single highest-priority classification label matching a file.
	 * Returns the first label from getClassificationLabelsForFile() or null if none match.
	 *
	 * @param File $file
	 * @return IClassificationLabel|null The highest-priority matching label, or null
	 */
	public function getClassificationLabelForFile(File $file) : ?IClassificationLabel {
		$labels = $this->getClassificationLabelsForFile($file);
		return $labels[0] ?? null;
	}

	/**
	 * Get all matching classification labels for a file, sorted by priority (highest first).
	 * Unlike getClassificationLabelForFile() which returns only the top match,
	 * this method returns every label whose rules matched the file content,
	 * metadata, or BAILS policy — enabling multi-tag assignment.
	 *
	 * @param File $file
	 * @return IClassificationLabel[] All matching labels sorted by priority desc, then index asc
	 */
	public function getClassificationLabelsForFile(File $file): array {
		$labels = $this->settings->getClassificationLabels();
		if (empty($labels)) {
			return [];
		}

		$bailsPolicy = $this->bailsService->getPolicyForFile($file);
		$labelsFromPolicy = [];
		if ($bailsPolicy !== null) {
			foreach ($labels as $label) {
				if (count($label->getBailsCategories()) === 0) {
					continue;
				}
				$allCategoriesMatch = true;
				foreach ($label->getBailsCategories() as $categoryId) {
					if (!in_array($categoryId, array_map(static fn ($cat) => $cat->getId(), $bailsPolicy->getCategories()), true)) {
						$allCategoriesMatch = false;
						break;
					}
				}
				if ($allCategoriesMatch) {
					$labelsFromPolicy[] = $label;
				}
			}
		}

		$metadata = $this->metadataService->getMetadataForFile($file);
		$labelsFromMetadata = $this->findAllLabelsInMetadata($metadata, $labels);

		$labelsFromContent = $this->findAllLabelsInStream($file, $labels);

		/** @var IClassificationLabel[] $foundLabels */
		$foundLabels = array_values(array_unique(
			array_merge($labelsFromPolicy, $labelsFromMetadata, $labelsFromContent),
			SORT_REGULAR
		));

		if (count($foundLabels) === 0) {
			return [];
		}

		// Sort by priority descending (higher priority first), then by index ascending as tiebreaker
		usort($foundLabels, function (IClassificationLabel $label1, IClassificationLabel $label2) {
			if ($label1->getPriority() !== $label2->getPriority()) {
				return $label2->getPriority() <=> $label1->getPriority();
			}
			return $label1->getIndex() <=> $label2->getIndex();
		});

		return $foundLabels;
	}

	/**
	 * Find all labels matching metadata
	 *
	 * @param MetadataItem[] $metadataItems
	 * @param list<IClassificationLabel> $labels
	 * @return IClassificationLabel[]
	 */
	private function findAllLabelsInMetadata(array $metadataItems, array $labels): array {
		$matched = [];
		foreach ($labels as $label) {
			if (count($label->getMetadataItems()) === 0) {
				continue;
			}
			$matchedKeys = 0;
			$allMatch = true;
			foreach ($label->getMetadataItems() as $labelMetadataItem) {
				$keyFound = false;
				foreach ($metadataItems as $fileMetadataItem) {
					if ($labelMetadataItem->getKey() === $fileMetadataItem->getKey()) {
						$keyFound = true;
						$matchedKeys++;
						if ($labelMetadataItem->getValue() !== $fileMetadataItem->getValue()) {
							$allMatch = false;
							break 2;
						}
					}
				}
				if (!$keyFound) {
					$allMatch = false;
					break;
				}
			}
			if ($allMatch && count($label->getMetadataItems()) === $matchedKeys && $matchedKeys > 0) {
				$matched[] = $label;
			}
		}
		return $matched;
	}

	/**
	 * Find all matching labels in the file content stream
	 *
	 * @param IClassificationLabel[] $labels
	 * @return IClassificationLabel[]
	 */
	private function findAllLabelsInStream(File $file, array $labels): array {
		$patterns = [];
		$captureMap = [];
		$maxMatchLength = 0;

		foreach ($labels as $i => $label) {
			$maxMatchLength = max($maxMatchLength, $label->getMaxMatchLength());

			foreach ($label->getKeywords() as $j => $keyword) {
				if (empty($keyword)) {
					continue;
				}
				$captureName = "L{$i}K{$j}";
				// Keywords are case-insensitive
				$patterns[] = '(?<' . $captureName . '>' . preg_quote($keyword, '/') . ')';
				$captureMap[$captureName] = $label;
			}
			foreach ($label->getSearchExpressions() as $j => $expression) {
				$pattern = $this->matcherService->getMatchExpression($expression);
				if ($pattern !== null && $pattern !== '') {
					$captureName = "L{$i}S{$j}";
					// Remove delimiters from the pattern provided by MatcherService
					$patterns[] = '(?<' . $captureName . '>' . trim($pattern, '/') . ')';
					$captureMap[$captureName] = $label;
				}
			}
			foreach ($label->getRegularExpressions() as $j => $pattern) {
				if (empty($pattern)) {
					continue;
				}
				$captureName = "L{$i}R{$j}";
				$patterns[] = '(?<' . $captureName . '>' . $pattern . ')';
				$captureMap[$captureName] = $label;
			}
		}

		if (empty($patterns)) {
			return [];
		}

		$combinedRegex = '/' . implode('|', $patterns) . '/isu';
		$overlapSize = $maxMatchLength > 0 ? $maxMatchLength - 1 : 0;

		$contentStream = $this->contentService->getContentStreamForFile($file);
		$overlap = '';
		$matchedLabels = [];

		foreach ($contentStream as $chunk) {
			$textToSearch = $overlap . $chunk;
			$matches = [];

			if (@preg_match_all($combinedRegex, $textToSearch, $matches) > 0) {
				foreach ($captureMap as $captureName => $label) {
					if (!empty($matches[$captureName]) && array_filter($matches[$captureName], fn ($m) => $m !== '')) {
						$matchedLabels[spl_object_id($label)] = $label;
					}
				}
			}

			if ($overlapSize > 0) {
				$overlap = substr($textToSearch, -$overlapSize);
			}
		}

		// Check the final overlap for any trailing matches
		if (!empty($overlap)) {
			$matches = [];
			if (@preg_match_all($combinedRegex, $overlap, $matches) > 0) {
				foreach ($captureMap as $captureName => $label) {
					if (!empty($matches[$captureName]) && array_filter($matches[$captureName], fn ($m) => $m !== '')) {
						$matchedLabels[spl_object_id($label)] = $label;
					}
				}
			}
		}

		return array_values($matchedLabels);
	}
}
