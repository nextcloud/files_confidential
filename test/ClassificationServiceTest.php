<?php

/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

use OCA\Files_Confidential\Model\BailsAuthorizationCategory;
use OCA\Files_Confidential\Model\BailsPolicy;
use OCA\Files_Confidential\Model\ClassificationLabel;
use OCA\Files_Confidential\Model\MetadataItem;
use OCA\Files_Confidential\Service\BailsPolicyProviderService;
use OCA\Files_Confidential\Service\ClassificationService;
use OCA\Files_Confidential\Service\ContentProviderService;
use OCA\Files_Confidential\Service\MatcherService;
use OCA\Files_Confidential\Service\MetadataProviderService;
use OCA\Files_Confidential\Service\SettingsService;
use OCP\Files\File;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

/**
 * @group DB
 */
class ClassificationServiceTest extends TestCase {
	private ContentProviderService|MockObject $contentProvider;
	private MetadataProviderService|MockObject $metadataProvider;
	private BailsPolicyProviderService|MockObject $bailsProvider;
	private SettingsService|MockObject $settings;
	private MatcherService|MockObject $matcherService;
	private ClassificationService $classificationService;


	public function setUp(): void {
		parent::setUp();
		$this->contentProvider = $this->createMock(ContentProviderService::class);
		$this->metadataProvider = $this->createMock(MetadataProviderService::class);
		$this->bailsProvider = $this->createMock(BailsPolicyProviderService::class);
		$this->settings = $this->createMock(SettingsService::class);
		$this->matcherService = $this->createMock(MatcherService::class);

		$this->classificationService = new ClassificationService(
			$this->contentProvider,
			$this->metadataProvider,
			$this->bailsProvider,
			$this->settings,
			$this->matcherService
		);
	}

	private function createGenerator(string $content, int $chunkSize = 8192): Generator {
		return (static function () use ($content, $chunkSize) {
			for ($i = 0, $iMax = strlen($content); $i < $iMax; $i += $chunkSize) {
				yield substr($content, $i, $chunkSize);
			}
		})();
	}

	public function testContentClassificationPositive() : void {
		$this->contentProvider->expects($this->once())
			->method('getContentStreamForFile')
			->willReturn($this->createGenerator('This is my IBAN: AL35202111090000000001234567'));
		$this->matcherService->expects($this->any())
			->method('getMatchExpression')
			->willReturnMap([
				['IBAN', '/\b([A-Z]{2}[ \-]?[0-9]{2})(?=(?:[ \-]?[A-Z0-9]){9,30}\b)((?:[ \-]?[A-Z0-9]{3,5}){2,7})([ \-]?[A-Z0-9]{1,3})?\b/'],
				['E-Mail', '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}\b/'],
			]);

		$this->metadataProvider->expects($this->once())->method('getMetadataForFile')->willReturn([]);
		$this->bailsProvider->expects($this->once())->method('getPolicyForFile')->willReturn(null);

		$targetLabel = new ClassificationLabel(0, 'CONFIDENTIAL', [], [], ['IBAN'], [], []);
		$distractorLabel = new ClassificationLabel(1, 'foo', [], [], ['E-Mail'], [], []);
		$this->settings->expects($this->once())->method('getClassificationLabels')->willReturn([
			$targetLabel,
			$distractorLabel,
		]);

		$predictedLabel = $this->classificationService->getClassificationLabelForFile($this->createMock(File::class));
		self::assertSame($targetLabel, $predictedLabel);
	}

	public function testContentClassificationNegative() : void {
		$this->contentProvider->expects($this->once())
			->method('getContentStreamForFile')
			->willReturn($this->createGenerator('This is not my IBAN: L35201234567'));
		$this->matcherService->expects($this->any())
			->method('getMatchExpression')
			->with('IBAN')
			->willReturn('/\b([A-Z]{2}[ \-]?[0-9]{2})(?=(?:[ \-]?[A-Z0-9]){9,30}\b)((?:[ \-]?[A-Z0-9]{3,5}){2,7})([ \-]?[A-Z0-9]{1,3})?\b/');


		$this->metadataProvider->expects($this->once())->method('getMetadataForFile')->willReturn([]);
		$this->bailsProvider->expects($this->once())->method('getPolicyForFile')->willReturn(null);

		$someLabel = new ClassificationLabel(0, 'CONFIDENTIAL', [], [], ['IBAN'], [], []);
		$this->settings->expects($this->once())->method('getClassificationLabels')->willReturn([$someLabel]);

		$predictedLabel = $this->classificationService->getClassificationLabelForFile($this->createMock(File::class));
		self::assertNull($predictedLabel);
	}

	public function testMultipleLabelsFoundReturnsFirstMatch() : void {
		$content = 'This document is about email: test@example.com and is CONFIDENTIAL';
		$this->contentProvider->expects($this->once())
			->method('getContentStreamForFile')
			->willReturn($this->createGenerator($content));
		$this->matcherService->expects($this->any())
			->method('getMatchExpression')
			->with('E-Mail')
			->willReturn('/[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}/');

		$this->metadataProvider->expects($this->once())->method('getMetadataForFile')->willReturn([]);
		$this->bailsProvider->expects($this->once())->method('getPolicyForFile')->willReturn(null);

		$label1 = new ClassificationLabel(0, 'tag1', ['CONFIDENTIAL'], [], [], [], []);
		$label2 = new ClassificationLabel(1, 'tag2', [], [], ['E-Mail'], [], []);
		$this->settings->expects($this->once())->method('getClassificationLabels')->willReturn([$label1, $label2]);

		$predictedLabel = $this->classificationService->getClassificationLabelForFile($this->createMock(File::class));
		self::assertSame($label2, $predictedLabel);
	}

	public function testMetadataClassificationPositive() : void {
		$this->contentProvider->expects($this->never())->method('getContentStreamForFile');
		$this->metadataProvider->expects($this->once())->method('getMetadataForFile')->willReturn([
			new MetadataItem('RESTRICTION', 'CONFIDENTIAL'),
			new MetadataItem('CONFIDENTIALITY-LEVEL', '5'),
			new MetadataItem('SOMETHING-ELSE', '10'),
		]);
		$this->bailsProvider->expects($this->once())->method('getPolicyForFile')->willReturn(null);
		$targetLabel = new ClassificationLabel(0, 'CONFIDENTIAL', [], [], [], [], [
			new MetadataItem('RESTRICTION', 'CONFIDENTIAL'),
			new MetadataItem('CONFIDENTIALITY-LEVEL', '5'),
		]);
		$distractorLabel = new ClassificationLabel(1, 'foo', [], [], [], [], [
			new MetadataItem('RESTRICTION', 'NONE'),
			new MetadataItem('CONFIDENTIALITY-LEVEL', '0'),
		]);
		$this->settings->expects($this->once())->method('getClassificationLabels')->willReturn([
			$targetLabel,
			$distractorLabel,
		]);
		$predictedLabel = $this->classificationService->getClassificationLabelForFile($this->createMock(File::class));
		self::assertSame($targetLabel, $predictedLabel);
	}

	public function testMetadataClassificationNegative() : void {
		$this->contentProvider->expects($this->never())->method('getContentStreamForFile');
		$this->metadataProvider->expects($this->once())->method('getMetadataForFile')->willReturn([
			new MetadataItem('RESTRICTION', 'NONE'),
		]);
		$this->bailsProvider->expects($this->once())->method('getPolicyForFile')->willReturn(null);
		$someLabel = new ClassificationLabel(0, 'CONFIDENTIAL', [], [], [], [], [new MetadataItem('RESTRICTION', 'CONFIDENTIAL')]);
		$this->settings->expects($this->once())->method('getClassificationLabels')->willReturn([
			$someLabel,
		]);
		$predictedLabel = $this->classificationService->getClassificationLabelForFile($this->createMock(File::class));
		self::assertNull($predictedLabel);
	}

	public function testMetadataClassificationNegative2() : void {
		$this->contentProvider->expects($this->never())->method('getContentStreamForFile');
		$this->metadataProvider->expects($this->once())->method('getMetadataForFile')->willReturn([
			new MetadataItem('RESTRICTION', 'CONFIDENTIAL'),
			new MetadataItem('LEVEL', '1'),
		]);
		$this->bailsProvider->expects($this->once())->method('getPolicyForFile')->willReturn(null);
		$someLabel = new ClassificationLabel(0, 'CONFIDENTIAL', [], [], [], [], [
			new MetadataItem('RESTRICTION', 'CONFIDENTIAL'),
			new MetadataItem('LEVEL', '2'),
		]);
		$this->settings->expects($this->once())->method('getClassificationLabels')->willReturn([
			$someLabel,
		]);
		$predictedLabel = $this->classificationService->getClassificationLabelForFile($this->createMock(File::class));
		self::assertNull($predictedLabel);
	}

	public function testBailsClassificationPositive() : void {
		$this->contentProvider->expects($this->never())->method('getContentStreamForFile');
		$this->metadataProvider->expects($this->once())->method('getMetadataForFile')->willReturn([]);
		$policy = new BailsPolicy();
		$policy->setId('The one true policy');
		$category1 = new BailsAuthorizationCategory();
		$category1->setId('distractorCategory');
		$policy->addCategory($category1);
		$category2 = new BailsAuthorizationCategory();
		$category2->setId('SECRET');
		$policy->addCategory($category2);
		$this->bailsProvider->expects($this->once())->method('getPolicyForFile')->willReturn(
			$policy
		);
		$targetLabel = new ClassificationLabel(0, 'CONFIDENTIAL', [], ['SECRET'], [], [], []);
		$distractorLabel = new ClassificationLabel(1, 'foo', [], ['BLABLA'], [], [], []);
		$this->settings->expects($this->once())->method('getClassificationLabels')->willReturn([
			$targetLabel,
			$distractorLabel,
		]);
		$predictedLabel = $this->classificationService->getClassificationLabelForFile($this->createMock(File::class));
		self::assertSame($targetLabel, $predictedLabel);
	}

	public function testBailsClassificationNegative() : void {
		$this->contentProvider->expects($this->never())->method('getContentStreamForFile');
		$this->metadataProvider->expects($this->once())->method('getMetadataForFile')->willReturn([]);
		$policy = new BailsPolicy();
		$policy->setId('The one true policy');
		$category = new BailsAuthorizationCategory();
		$category->setId('none');
		$policy->addCategory($category);
		$this->bailsProvider->expects($this->once())->method('getPolicyForFile')->willReturn(
			$policy
		);
		$someLabel = new ClassificationLabel(0, 'CONFIDENTIAL', [], ['SECRET'], [], [], []);
		$this->settings->expects($this->once())->method('getClassificationLabels')->willReturn([$someLabel]);

		$predictedLabel = $this->classificationService->getClassificationLabelForFile($this->createMock(File::class));
		self::assertNull($predictedLabel);
	}

	public function testClassificationWithMatchAcrossChunks(): void {
		$keyword = 'SUPER_SECRET_KEYWORD';
		$content = str_repeat('A', 90) . $keyword . str_repeat('B', 500);

		$this->contentProvider->expects($this->once())
			->method('getContentStreamForFile')
			->willReturn($this->createGenerator($content, 100)); // Chunk size 100, keyword will be split

		$this->metadataProvider->expects($this->once())->method('getMetadataForFile')->willReturn([]);
		$this->bailsProvider->expects($this->once())->method('getPolicyForFile')->willReturn(null);

		$targetLabel = new ClassificationLabel(0, 'SECRET', [$keyword], [], [], [], []);
		$this->settings->expects($this->once())->method('getClassificationLabels')->willReturn([$targetLabel]);

		$predictedLabel = $this->classificationService->getClassificationLabelForFile($this->createMock(File::class));
		self::assertSame($targetLabel, $predictedLabel);
	}
}
