<?php

namespace OCA\Files_Confidential\Model;

use DeepCopy\Matcher\Matcher;
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

	public static function findLabelsInText(string $text, array $labels): ?IClassificationLabel {
		$matcherService = MatcherService::getInstance();
		foreach ($labels as $label) {
			foreach ($label->getKeywords() as $keyword) {
				if (stripos($text, $keyword) !== false) {
					return $label;
				}
			}
			foreach($label->getSearchExpressions() as $expression) {
				$pattern = $matcherService->getMatchExpression($expression);
				if (preg_match($pattern, $text) !== false) {
					return $label;
				}
			}
		}
		return null;
	}

	public function __construct(int $index, string $tag, array $keywords, array $categories, array $searchExpressions) {
		$this->index = $index;
		$this->tag = $tag;
		$this->keywords = $keywords;
		$this->categories = $categories;
		$this->searchExpressions = $searchExpressions;
	}

	/**
	 * @param array{index:int, tag:string, keywords:list<string>, categories:list<string>} $labelRaw
	 * @return \OCA\Files_Confidential\Model\ClassificationLabel
	 * @throws \ValueError
	 */
	public static function fromArray(array $labelRaw): ClassificationLabel {
		if (!isset($labelRaw['index'], $labelRaw['tag'], $labelRaw['keywords'], $labelRaw['categories'], $labelRaw['searchExpressions'])) {
			throw new \ValueError();
		}
		return new ClassificationLabel($labelRaw['index'], $labelRaw['tag'], $labelRaw['keywords'], $labelRaw['categories'], $labelRaw['searchExpressions']);
	}

	public function toArray() : array {
		return [
			'index' => $this->getIndex(),
			'tag' => $this->getTag(),
			'keywords' => $this->getKeywords(),
			'categories' => $this->getBailsCategories(),
			'searchExpressions' => $this->getSearchExpressions()
		];
	}

	public static function getDefaultLabels() {
		return array_map(fn ($label) => ClassificationLabel::fromArray($label), [
			['index' => 0, 'tag' => 'Top secret', 'keywords' => ['top secret'], 'categories' => [], 'searchExpressions' => []],
			['index' => 1, 'tag' => 'Secret', 'keywords' => ['secret'], 'categories' => [],  'searchExpressions' => []],
			['index' => 2, 'tag' => 'Confidential', 'keywords' => ['confidential'], 'categories' => [], 'searchExpressions' => []],
			['index' => 3, 'tag' => 'Restricted', 'keywords' => ['restricted'], 'categories' => [], 'searchExpressions' => []],
		]);
	}

	public function getIndex(): int {
		return $this->index;
	}

	public function getTag(): string {
		return $this->tag;
	}

	public function getKeywords(): array {
		return $this->keywords;
	}

	public function getBailsCategories(): array {
		return $this->categories;
	}

	public function getSearchExpressions(): array {
		return $this->searchExpressions;
	}
}
