<?php

declare(strict_types=1);

namespace OCA\Files_Confidential\Service;

use OCA\Files_Confidential\Contract\IClassificationLabel;
use OCA\Files_Confidential\Model\ClassificationLabel;
use OCP\Files\File;

class ClassificationService {
	public function __construct(
		private ContentProviderService $contentService,
		private MetadataProviderService $metadataService,
		private BailsPolicyProviderService $bailsService,
		private SettingsService $settings
	) {
	}

	public function getClassificationLabelForFile(File $file) : ?IClassificationLabel {
		$labels = $this->settings->getClassificationLabels();

		$bailsPolicy = $this->bailsService->getPolicyForFile($file);
		$labelFromPolicy = null;
		if ($bailsPolicy !== null) {
			foreach ($labels as $label) {
				if (count($label->getBailsCategories()) === 0) {
					continue;
				}
				foreach ($label->getBailsCategories() as $categoryId) {
					// All defined categories for this label must be assigned to the document for the label to be applied
					if (!in_array($categoryId, array_map(fn ($cat) => $cat->getId(), $bailsPolicy->getCategories()))) {
						continue 2;
					}
				}
				$labelFromPolicy = $label;
			}
		}

		$metadata = $this->metadataService->getMetadataForFile($file);
		$labelFromMetadata = ClassificationLabel::findLabelsInMetadata($metadata, $labels);

		$content = $this->contentService->getContentForFile($file);
		$labelFromContent = ClassificationLabel::findLabelsInText($content, $labels);

		$labels = array_values(array_filter([$labelFromMetadata, $labelFromPolicy, $labelFromContent], fn($label) => $label !== null));

		if (count($labels) === 0) {
			return null;
		}

		usort($labels, function ($label1, $label2) {
			return $label1->getIndex() <=> $label2->getIndex();
		});

		return $labels[0];
	}
}
