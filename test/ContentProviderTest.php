<?php

use OCA\Files_Confidential\ContentProviders\MicrosoftContentProvider;
use OCA\Files_Confidential\ContentProviders\OpenDocumentContentProvider;
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

	/**
	 * @dataProvider openDocumentContentDataProvider
	 * @return void
	 * @throws \OCP\AppFramework\QueryException
	 * @throws \OCP\Files\NotPermittedException
	 */
	public function testOpenDocumentProvider(string $file) : void {
		$this->testFile = $this->userFolder->newFile('/test.odt', file_get_contents(__DIR__ . '/res/'.$file));
		/** @var \OCA\Files_Confidential\Contract\IContentProvider $provider */
		$provider = \OC::$server->get(OpenDocumentContentProvider::class);
		$content = $provider->getContentForFile($this->testFile);
		$this->assertStringContainsStringIgnoringCase('top secret', $content);
	}

	/**
	 * @dataProvider microsoftContentDataProvider
	 * @return void
	 * @throws \OCP\AppFramework\QueryException
	 * @throws \OCP\Files\NotPermittedException
	 */
	public function testMicrosoftWatermark(string $file) : void {
		$this->testFile = $this->userFolder->newFile('/test.docx', file_get_contents(__DIR__ . '/res/'.$file));
		/** @var \OCA\Files_Confidential\Contract\IContentProvider $provider */
		$provider = \OC::$server->get(MicrosoftContentProvider::class);
		$content = $provider->getContentForFile($this->testFile);
		$this->assertStringContainsStringIgnoringCase('top secret', $content);
	}

	public function microsoftContentDataProvider() {
		return [
			['test_watermark_top_secret.docx'],
			['test_header_top_secret.docx'],
			['test_footer_top_secret.docx'],
		];
	}

	public function openDocumentContentDataProvider() {
		return [
			['test_watermark_top_secret.odt'],
			['test_header_top_secret.odt'],
			['test_footer_top_secret.odt'],
		];
	}

	private function loginAndGetUserFolder(string $userId) {
		$this->loginAsUser($userId);
		return $this->rootFolder->getUserFolder($userId);
	}
}
