<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Files_Confidential\Service;

use OCA\Files_Confidential\Contract\IClassificationLabel;
use OCA\Files_Confidential\Model\ClassificationLabel;
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

	public function getClassificationLabelForFile(File $file) : ?IClassificationLabel {
		$labels = $this->settings->getClassificationLabels();
		if (empty($labels)) {
			return null;
		}

		$bailsPolicy = $this->bailsService->getPolicyForFile($file);
		$labelFromPolicy = null;
		if ($bailsPolicy !== null) {
			foreach ($labels as $label) {
				if (count($label->getBailsCategories()) === 0) {
					continue;
				}
				foreach ($label->getBailsCategories() as $categoryId) {
					// All defined categories for this label must be assigned to the document for the label to be applied
					if (!in_array($categoryId, array_map(static fn ($cat) => $cat->getId(), $bailsPolicy->getCategories()), true)) {
						continue 2;
					}
				}
				$labelFromPolicy = $label;
			}
		}

		$metadata = $this->metadataService->getMetadataForFile($file);
		$labelFromMetadata = ClassificationLabel::findLabelsInMetadata($metadata, $labels);

		$labelFromContent = $this->findLabelInStream($file, $labels);

		/** @var IClassificationLabel[] $foundLabels */
		$foundLabels = array_values(array_filter([$labelFromMetadata, $labelFromPolicy, $labelFromContent], static fn ($label) => $label !== null));

		if (count($foundLabels) === 0) {
			return null;
		}

		usort($foundLabels, function (IClassificationLabel $label1, IClassificationLabel $label2) {
			return $label1->getIndex() <=> $label2->getIndex();
		});

		return $foundLabels[0];
	}

	/**
	 * @param IClassificationLabel[] $labels
	 */
	private function findLabelInStream(File $file, array $labels): ?IClassificationLabel {
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
			return null;
		}

		$combinedRegex = '/' . implode('|', $patterns) . '/isu';
		$overlapSize = $maxMatchLength > 0 ? $maxMatchLength - 1 : 0;

		$contentStream = $this->contentService->getContentStreamForFile($file);
		$overlap = '';

		foreach ($contentStream as $chunk) {
			$textToSearch = $overlap . $chunk;
			$matches = [];

			if (@preg_match($combinedRegex, $textToSearch, $matches) === 1) {
				foreach ($captureMap as $captureName => $label) {
					if (!empty($matches[$captureName])) {
						// The first populated capture group corresponds to the highest-priority match.
						return $label;
					}
				}
			}

			if ($overlapSize > 0) {
				// Keep the last part of the text to search for overlaps in the next chunk
				$overlap = substr($textToSearch, -$overlapSize);
			}
		}

		// After the loop if we still here, check the final overlap for any trailing matches.
		if (!empty($overlap)) {
			$matches = [];
			if (@preg_match($combinedRegex, $overlap, $matches) === 1) {
				foreach ($captureMap as $captureName => $label) {
					if (!empty($matches[$captureName])) {
						return $label;
					}
				}
			}
		}

		return null;
	}
}
