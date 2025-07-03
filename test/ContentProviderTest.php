<?php

/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

use OCA\Files_Confidential\Model\ClassificationLabel;
use OCA\Files_Confidential\Providers\ContentProviders\MicrosoftContentProvider;
use OCA\Files_Confidential\Providers\ContentProviders\OpenDocumentContentProvider;
use OCA\Files_Confidential\Providers\ContentProviders\PdfContentProvider;
use OCA\Files_Confidential\Providers\ContentProviders\PlainTextContentProvider;
use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use Test\TestCase;

/**
 * @group DB
 */
class ContentProviderTest extends TestCase {
	public const TEST_USER1 = 'test-user1';

	private OCP\Files\File $testFile;
	private IRootFolder $rootFolder;
	private Folder $userFolder;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		$backend = new \Test\Util\User\Dummy();
		$backend->createUser(self::TEST_USER1, self::TEST_USER1);
		\OC::$server->get(\OCP\IUserManager::class)->registerBackend($backend);
	}

	public function setUp(): void {
		parent::setUp();
		$this->rootFolder = \OC::$server->getRootFolder();
		$this->userFolder = $this->loginAndGetUserFolder(self::TEST_USER1);
	}

	private function getContentFromStream(\Generator $stream): string {
		$content = '';
		foreach ($stream as $chunk) {
			$content .= $chunk;
		}
		return $content;
	}

	/**
	 * @dataProvider openDocumentContentDataProvider
	 * @return void
	 * @throws \OCP\AppFramework\QueryException
	 * @throws \OCP\Files\NotPermittedException
	 */
	public function testOpenDocumentProvider(string $file) : void {
		$this->testFile = $this->userFolder->newFile('/test.odt', file_get_contents(__DIR__ . '/res/' . $file));
		/** @var \OCA\Files_Confidential\Contract\IContentProvider $provider */
		$provider = \OC::$server->get(OpenDocumentContentProvider::class);
		$content = $this->getContentFromStream($provider->getContentStream($this->testFile));
		$this->assertStringContainsStringIgnoringCase('top secret', $content);
	}

	/**
	 * @dataProvider microsoftContentDataProvider
	 * @return void
	 * @throws \OCP\AppFramework\QueryException
	 * @throws \OCP\Files\NotPermittedException
	 */
	public function testMicrosoftProvider(string $file) : void {
		$this->testFile = $this->userFolder->newFile('/test.docx', file_get_contents(__DIR__ . '/res/' . $file));
		/** @var \OCA\Files_Confidential\Contract\IContentProvider $provider */
		$provider = \OC::$server->get(MicrosoftContentProvider::class);
		$content = $this->getContentFromStream($provider->getContentStream($this->testFile));
		$this->assertStringContainsStringIgnoringCase('top secret', $content);
	}

	/**
	 * @dataProvider openDocumentSearchDataProvider
	 * @param string $file
	 * @return void
	 * @throws \OCP\AppFramework\QueryException
	 * @throws \OCP\Files\NotPermittedException
	 */
	public function testOpenDocumentSearchExpressions(string $file) : void {
		$this->testFile = $this->userFolder->newFile('/test.odt', file_get_contents(__DIR__ . '/res/' . $file));
		/** @var \OCA\Files_Confidential\Contract\IContentProvider $provider */
		$provider = \OC::$server->get(OpenDocumentContentProvider::class);
		$content = $this->getContentFromStream($provider->getContentStream($this->testFile));

		$label = new ClassificationLabel(0, 'Protected', ['protected'], [], ['IBAN'], [], []);
		$foundLabel = ClassificationLabel::findLabelsInText($content, [$label]);
		$this->assertEquals($label, $foundLabel);
	}

	/**
	 * @dataProvider plainTextDataProvider
	 * @param string $file
	 * @return void
	 * @throws \OCP\Files\NotPermittedException
	 */
	public function testPlainTextSearchExpressions(string $file) : void {
		$this->testFile = $this->userFolder->newFile('/test.txt', file_get_contents(__DIR__ . '/res/' . $file));
		/** @var \OCA\Files_Confidential\Contract\IContentProvider $provider */
		$provider = \OC::$server->get(PlainTextContentProvider::class);
		$content = $this->getContentFromStream($provider->getContentStream($this->testFile));

		$label = new ClassificationLabel(0, 'Protected', ['protected'], [], ['IBAN'], [], []);
		$foundLabel = ClassificationLabel::findLabelsInText($content, [$label]);
		$this->assertEquals($label, $foundLabel);
	}

	/**
	 * @dataProvider pdfDataProvider
	 * @param string $file
	 * @return void
	 * @throws \OCP\Files\NotPermittedException
	 */
	public function testPdfSearchExpressions(string $file) : void {
		$this->testFile = $this->userFolder->newFile('/test.pdf', file_get_contents(__DIR__ . '/res/' . $file));
		/** @var \OCA\Files_Confidential\Contract\IContentProvider $provider */
		$provider = \OC::$server->get(PdfContentProvider::class);
		$content = $this->getContentFromStream($provider->getContentStream($this->testFile));

		$label = new ClassificationLabel(0, 'Protected', ['protected'], [], ['IBAN'], [], []);
		$foundLabel = ClassificationLabel::findLabelsInText($content, [$label]);
		$this->assertEquals($label, $foundLabel);
	}

	public function microsoftContentDataProvider() {
		return [
			['test_watermark_top_secret.docx'],
			['test_header_top_secret.docx'],
			['test_third_header_top_secret.docx'],
			['test_footer_top_secret.docx'],
			['test_third_footer_top_secret.docx'],
		];
	}

	public function openDocumentContentDataProvider() {
		return [
			['test_watermark_top_secret.odt'],
			['test_header_top_secret.odt'],
			['test_footer_top_secret.odt'],
		];
	}

	public function openDocumentSearchDataProvider() {
		return [
			['test_iban.odt'],
		];
	}

	public function plainTextDataProvider() {
		return [
			['test_iban.txt'],
		];
	}

	public function pdfDataProvider() {
		return [
			['test_iban.pdf'],
		];
	}

	private function loginAndGetUserFolder(string $userId) {
		self::loginAsUser($userId);
		return $this->rootFolder->getUserFolder($userId);
	}
}
