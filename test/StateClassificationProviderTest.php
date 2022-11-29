<?php

use OCA\Files_Confidential\Contract\IStateClassification;
use OCA\Files_Confidential\StateClassificationProviders\MicrosoftStateClassificationProvider;
use OCA\Files_Confidential\StateClassificationProviders\OpenDocumentStateClassificationProvider;
use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use Test\TestCase;

/**
 * @group DB
 */
class StateClassificationProviderTest extends TestCase {
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

	public function testOpenDocumentProvider() : void {
		$this->testFile = $this->userFolder->newFile('/test.odt', file_get_contents(__DIR__ . '/res/test_watermark_top_secret.odt'));
		/** @var \OCA\Files_Confidential\Contract\IStateClassificationProvider $provider */
		$provider = \OC::$server->get(OpenDocumentStateClassificationProvider::class);
		$stateClassification = $provider->getClassificationForFile($this->testFile);
		$this->assertEquals(IStateClassification::TOP_SECRET, $stateClassification);
	}

	public function testMicrosoftProvider() : void {
		$this->testFile = $this->userFolder->newFile('/test.docx', file_get_contents(__DIR__ . '/res/test_watermark_top_secret.docx'));
		/** @var \OCA\Files_Confidential\Contract\IStateClassificationProvider $provider */
		$provider = \OC::$server->get(MicrosoftStateClassificationProvider::class);
		$stateClassification = $provider->getClassificationForFile($this->testFile);
		$this->assertEquals(IStateClassification::TOP_SECRET, $stateClassification);
	}

	private function loginAndGetUserFolder(string $userId) {
		$this->loginAsUser($userId);
		return $this->rootFolder->getUserFolder($userId);
	}
}
