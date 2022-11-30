<?php

namespace OCA\Files_Confidential\Service;

use OCA\Files_Confidential\Contract\IClassificationLabel;
use OCA\Files_Confidential\Model\ClassificationLabel;

class ClassificationService {

	private ContentProviderService $contentService;
	private BailsPolicyProviderService $bailsService;
	private SettingsService $settings;

	public function __construct(ContentProviderService $contentService, BailsPolicyProviderService $bailsService, SettingsService $settings)
	{
		$this->contentService = $contentService;
		$this->bailsService = $bailsService;
		$this->settings = $settings;
	}

	public function getClassificationLabelForFile(\OCP\Files\File $file) : ?IClassificationLabel{
		$labels = $this->settings->getClassificationLabels();

		$bailsPolicy = $this->bailsService->getPolicyForFile($file);
		$labelFromPolicy = null;
		foreach ($labels as $label) {
			foreach ($label->getBailsCategories() as $categoryId) {
				// All defined categories for this label must be assigned to the document for the label to be applied
				if (!in_array($categoryId, array_map(fn($cat) => $cat->getId(), $bailsPolicy->getCategories()))) {
					continue 2;
				}
			}
			$labelFromPolicy = $label;
		}

		$content = $this->contentService->getContentForFile($file);
		$labelFromContent = ClassificationLabel::findLabelsInText($content, $labels);

		if ($labelFromContent !== null) {
			if ($labelFromPolicy !== null) {
				if ($labelFromContent->getIndex() > $labelFromPolicy->getIndex()) {
					return $labelFromPolicy;
				}else {
					return $labelFromContent;
				}
			}else{
				return $labelFromContent;
			}
		}else{
			return null;
		}
	}
}
