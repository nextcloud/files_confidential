<?php

use OCA\Files_Confidential\Providers\BailsProviders\MicrosoftOfficeBailsProvider;
use OCA\Files_Confidential\Providers\BailsProviders\OpenDocumentBailsProvider;
use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use Test\TestCase;

/**
 * @group DB
 */
class MetadataProviderTest extends TestCase {
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

	public function testMicrosoftOfficeProvider() : void {
		$this->testFile = $this->userFolder->newFile('/test.pptx', file_get_contents(__DIR__ . '/res/test_mips_metadata.pptx'));
		/** @var \OCA\Files_Confidential\Contract\IMetadataProvider $provider */
		$provider = \OC::$server->get(\OCA\Files_Confidential\Providers\MetadataProviders\MicrosoftOfficeMetadataProvider::class);
		$metadataItems = $provider->getMetadataForFile($this->testFile);

		$array = array_map(fn($item) => $item->toArray(), $metadataItems);
		self::assertEquals([
			['key' => "MSIP_Label_9a7859fa-fb81-458d-9040-c3b7cffe6362_Enabled", 'value' => 'true'],
			['key' => "MSIP_Label_9a7859fa-fb81-458d-9040-c3b7cffe6362_SetDate", 'value' => '2024-01-18T23:42:17Z'],
			['key' => "MSIP_Label_9a7859fa-fb81-458d-9040-c3b7cffe6362_Method", 'value' => 'Privileged'],
			['key' => "MSIP_Label_9a7859fa-fb81-458d-9040-c3b7cffe6362_Name", 'value' => 'External'],
			['key' => "MSIP_Label_9a7859fa-fb81-458d-9040-c3b7cffe6362_SiteId", 'value' => '9255f64b-1818-42e5-ad78-f619a9a7b1e7'],
			['key' => "MSIP_Label_9a7859fa-fb81-458d-9040-c3b7cffe6362_ActionId", 'value' => '12d6f90a-22dd-42f4-8cc5-43177be44de3'],
			['key' => "MSIP_Label_9a7859fa-fb81-458d-9040-c3b7cffe6362_ContentBits", 'value' => '3'],
		], $array);
	}

	private function loginAndGetUserFolder(string $userId) {
		$this->loginAsUser($userId);
		return $this->rootFolder->getUserFolder($userId);
	}
}
