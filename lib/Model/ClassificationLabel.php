<?php

namespace OCA\Files_Confidential\Model;

use OCA\Files_Confidential\Contract\IClassificationLabel;

class ClassificationLabel implements IClassificationLabel {
	private string $name;
	private int $index;
	/**
	 * @var list<string>
	 */
	private array $keywords;
	/**
	 * @var list<string>
	 */
	private array $categories;

	public static function findLabelsInText(string $text, array $labels): ?IClassificationLabel {
		foreach($labels as $label) {
			foreach ($label->getKeywords() as $keyword) {
				if (stripos($text, $keyword) !== false) {
					return $label;
				}
			}
		}
		return null;
	}

	public function __construct(int $index, string $name, array $keywords, array $categories) {
		$this->index = $index;
		$this->name = $name;
		$this->keywords = $keywords;
		$this->categories = $categories;
	}

	/**
	 * @param array{index:int, name:string, keywords:list<string>, categories:list<string>} $labelRaw
	 * @return \OCA\Files_Confidential\Model\ClassificationLabel
	 * @throws \ValueError
	 */
	public static function fromArray(array $labelRaw): ClassificationLabel {
		if (!isset($labelRaw['index'], $labelRaw['name'], $labelRaw['keywords'], $labelRaw['categories'])) {
			throw new \ValueError();
		}
		return new ClassificationLabel($labelRaw['index'], $labelRaw['name'], $labelRaw['keywords'], $labelRaw['categories']);
	}

	public function getIndex(): int
	{
		return $this->index;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getKeywords(): array
	{
		return $this->keywords;
	}

	public function getBailsCategories(): array
	{
		return $this->categories;
	}
}
