<?php

use OCA\Files_Confidential\Model\BailsAuthorizationCategory;
use OCA\Files_Confidential\Model\BailsPolicy;
use OCA\Files_Confidential\Model\ClassificationLabel;
use OCA\Files_Confidential\Model\MetadataItem;
use OCA\Files_Confidential\Service\BailsPolicyProviderService;
use OCA\Files_Confidential\Service\ClassificationService;
use OCA\Files_Confidential\Service\ContentProviderService;
use OCA\Files_Confidential\Service\MetadataProviderService;
use OCA\Files_Confidential\Service\SettingsService;
use Test\TestCase;

/**
 * @group DB
 */
class ClassificationServiceTest extends TestCase {

	public function setUp(): void {
		parent::setUp();
		$this->contentProvider = $this->createMock(ContentProviderService::class);
		$this->metadataProvider = $this->createMock(MetadataProviderService::class);
		$this->bailsProvider = $this->createMock(BailsPolicyProviderService::class);
		$this->settings = $this->createMock(SettingsService::class);
		$this->classificationService = new ClassificationService($this->contentProvider, $this->metadataProvider, $this->bailsProvider, $this->settings);
	}

	public function testContentClassificationPositive() : void {
		$this->contentProvider->expects($this->once())->method('getContentForFile')->willReturn('This is my IBAN: AL35202111090000000001234567');
		$this->metadataProvider->expects($this->once())->method('getMetadataForFile')->willReturn([]);
		$this->bailsProvider->expects($this->once())->method('getPolicyForFile')->willReturn(null);
		$targetLabel = new ClassificationLabel(0, 'CONFIDENTIAL', [], [], ['IBAN'], [], []);
		$distractorLabel = new ClassificationLabel(0, 'foo', [], [], ['E-Mail'], [], []);
		$this->settings->expects($this->once())->method('getClassificationLabels')->willReturn([
			$targetLabel,
			$distractorLabel,
		]);
		$predictedLabel = $this->classificationService->getClassificationLabelForFile($this->createMock(\OCP\Files\File::class));
		self::assertEquals($targetLabel, $predictedLabel);
	}

	public function testContentClassificationNegative() : void {
		$this->contentProvider->expects($this->once())->method('getContentForFile')->willReturn('This is not my IBAN: L35201234567');
		$this->metadataProvider->expects($this->once())->method('getMetadataForFile')->willReturn([]);
		$this->bailsProvider->expects($this->once())->method('getPolicyForFile')->willReturn(null);
		$someLabel = new ClassificationLabel(0, 'CONFIDENTIAL', [], [], ['IBAN'], [], []);
		$this->settings->expects($this->once())->method('getClassificationLabels')->willReturn([
			$someLabel,
		]);
		$predictedLabel = $this->classificationService->getClassificationLabelForFile($this->createMock(\OCP\Files\File::class));
		self::assertNull($predictedLabel);
	}

	public function testMetadataClassificationPositive() : void {
		$this->contentProvider->expects($this->once())->method('getContentForFile')->willReturn('');
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
		$distractorLabel = new ClassificationLabel(0, 'foo', [], [], [], [], [
			new MetadataItem('RESTRICTION', 'NONDE'),
			new MetadataItem('CONFIDENTIALITY-LEVEL', '0'),
		]);
		$this->settings->expects($this->once())->method('getClassificationLabels')->willReturn([
			$targetLabel,
			$distractorLabel,
		]);
		$predictedLabel = $this->classificationService->getClassificationLabelForFile($this->createMock(\OCP\Files\File::class));
		self::assertEquals($targetLabel, $predictedLabel);
	}

	public function testMetadataClassificationNegative() : void {
		$this->contentProvider->expects($this->once())->method('getContentForFile')->willReturn('');
		$this->metadataProvider->expects($this->once())->method('getMetadataForFile')->willReturn([
			new MetadataItem('RESTRICTION', 'NONE'),
		]);
		$this->bailsProvider->expects($this->once())->method('getPolicyForFile')->willReturn(null);
		$someLabel = new ClassificationLabel(0, 'CONFIDENTIAL', [], [], [], [], [new MetadataItem('RESTRICTION', 'CONFIDENTIAL'),]);
		$this->settings->expects($this->once())->method('getClassificationLabels')->willReturn([
			$someLabel,
		]);
		$predictedLabel = $this->classificationService->getClassificationLabelForFile($this->createMock(\OCP\Files\File::class));
		self::assertNull($predictedLabel);
	}

	public function testMetadataClassificationNegative2() : void {
		$this->contentProvider->expects($this->once())->method('getContentForFile')->willReturn('');
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
		$predictedLabel = $this->classificationService->getClassificationLabelForFile($this->createMock(\OCP\Files\File::class));
		self::assertNull($predictedLabel);
	}

	public function testBailsClassificationPositive() : void {
		$this->contentProvider->expects($this->once())->method('getContentForFile')->willReturn('');
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
		$distractorLabel = new ClassificationLabel(0, 'foo', [], ['BLABLA'], [], [], []);
		$this->settings->expects($this->once())->method('getClassificationLabels')->willReturn([
			$targetLabel,
			$distractorLabel,
		]);
		$predictedLabel = $this->classificationService->getClassificationLabelForFile($this->createMock(\OCP\Files\File::class));
		self::assertEquals($targetLabel, $predictedLabel);
	}

	public function testBailsClassificationNegative() : void {
		$this->contentProvider->expects($this->once())->method('getContentForFile')->willReturn('');
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
		$this->settings->expects($this->once())->method('getClassificationLabels')->willReturn([
			$someLabel,
		]);
		$predictedLabel = $this->classificationService->getClassificationLabelForFile($this->createMock(\OCP\Files\File::class));
		self::assertNull($predictedLabel);
	}
}
