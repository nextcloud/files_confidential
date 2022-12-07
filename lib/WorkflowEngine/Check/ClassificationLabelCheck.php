<?php

namespace OCA\Files_Confidential\WorkflowEngine\Check;

use OCA\Files_Confidential\Service\ClassificationService;
use OCA\Files_Confidential\Service\SettingsService;
use OCA\WorkflowEngine\Entity\File;
use OCP\Files\Node;
use OCP\Files\Storage\IStorage;
use OCP\WorkflowEngine\ICheck;
use OCP\WorkflowEngine\IEntity;
use OCP\WorkflowEngine\IEntityCheck;

class ClassificationLabelCheck implements IEntityCheck, ICheck {
	public const UNCLASSIFIED_INDEX = 10000000000;

	protected IStorage $storage;
	protected string $path;
	private SettingsService $settings;
	private ClassificationService $classificationService;
	private ?Node $file;

	/**
	 * @param \OCA\Files_Confidential\Service\SettingsService $settings
	 * @param \OCA\Files_Confidential\Service\ClassificationService $classificationService
	 */
	public function __construct(SettingsService $settings, ClassificationService $classificationService) {
		$this->settings = $settings;
		$this->classificationService = $classificationService;
	}

	/**
	 * @inheritDoc
	 */
	public function executeCheck($operator, $value): bool {
		if (!$this->file instanceof \OCP\Files\File) {
			throw new \UnexpectedValueException(
				'Expected File got {class}',
				['class' => get_class($this->file)]
			);
		}
		$labels = $this->settings->getClassificationLabels();
		$labels = array_values(array_filter($labels, fn ($label) => $label->getIndex() === intval($value)));
		if (!isset($labels[0])) {
			return false;
		}

		$classification = $this->classificationService->getClassificationLabelForFile($this->file);
		if ($classification !== null) {
			$actualIndex = $classification->getIndex();
		} else {
			$actualIndex = self::UNCLASSIFIED_INDEX;
		}
		$comparedIndex = $labels[0]->getIndex();

		switch ($operator) {
			case 'less':
				return $actualIndex < $comparedIndex;
			case '!less':
				return $actualIndex >= $comparedIndex;
			case 'greater':
				return $actualIndex > $comparedIndex;
			case '!greater':
				return $actualIndex <= $comparedIndex;
			case 'equal':
				return $actualIndex === $comparedIndex;
			default:
				return false;
		}
	}

	/**
	 * @inheritDoc
	 */
	public function validateCheck($operator, $value) {
		if (!in_array($operator, ['less', '!less', 'greater', '!greater', 'equal'])) {
			throw new \UnexpectedValueException('The given operator is invalid', 1);
		}
		$value = intval($value);
		if (!in_array($value, array_map(fn ($label) => $label->getIndex(), $this->settings->getClassificationLabels()))) {
			throw new \UnexpectedValueException('The given label does not exist', 2);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function supportedEntities(): array {
		return [ File::class ];
	}

	/**
	 * @inheritDoc
	 */
	public function isAvailableForScope(int $scope): bool {
		return true;
	}

	/**
	 * @throws \OCP\Files\NotFoundException
	 */
	public function setEntitySubject(IEntity $entity, $subject): void {
		if ($entity instanceof File) {
			if (!$subject instanceof Node) {
				throw new \UnexpectedValueException(
					'Expected Node subject for File entity, got {class}',
					['class' => get_class($subject)]
				);
			}
			$this->storage = $subject->getStorage();
			$this->path = $subject->getPath();
			$this->file = $subject;
		}
	}
}
