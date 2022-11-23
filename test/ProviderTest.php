<?php

use OCA\Files_Confidential\Providers\MicrosoftOfficeProvider;
use OCA\Files_Confidential\Providers\OpenDocumentProvider;
use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use Test\TestCase;

/**
 * @group DB
 */
class ProviderTest extends TestCase {
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
		$this->testFile = $this->userFolder->newFile('/test.docx', file_get_contents(__DIR__.'/res/test.docx'));
		/** @var \OCA\Files_Confidential\Contract\IProvider $provider */
		$provider = \OC::$server->get(MicrosoftOfficeProvider::class);
		$policy = $provider->getPolicyForFile($this->testFile);

		$this->assertEquals('TSCP Example Policy', $policy->getName());
		$this->assertEquals('None', $policy->getId());
		$this->assertEquals('IntellectualProperty', $policy->getType());
		$this->assertEquals('TSCP Example Policy Authority', $policy->getAuthorityName());
		$this->assertEquals('None', $policy->getAuthorityId());
		$this->assertEquals('', $policy->getAuthorityCountry());
		$this->assertEquals('None', $policy->getAuthorizationName());
		$this->assertEquals('urn:example:tscp:1', $policy->getAuthorizationId());
		$this->assertEquals(new DateTime("2022-11-16T15:54:17"), $policy->getStartValidityDate());
		$this->assertEquals(null, $policy->getEndValidityDate());
		$this->assertEquals('3', $policy->getConfidentialityImpact());
		$this->assertEquals('3', $policy->getIntegrityImpact());
		$this->assertEquals('3', $policy->getAvailabilityImpact());
		$this->assertEquals('UK-Cabinet', $policy->getImpactScale());
		$this->assertCount(1, $policy->getCategories());
		$this->assertEquals('Internal Only', $policy->getCategories()[0]->getName());
		$this->assertEquals("urn:example:tscp:1:internal-only", $policy->getCategories()[0]->getId());
		$this->assertEquals("None", $policy->getCategories()[0]->getIdOID());
	}

	public function testOpenDocumentProvider() : void {
		$this->testFile = $this->userFolder->newFile('/test.docx', file_get_contents(__DIR__.'/res/test.odt'));
		/** @var \OCA\Files_Confidential\Contract\IProvider $provider */
		$provider = \OC::$server->get(OpenDocumentProvider::class);
		$policy = $provider->getPolicyForFile($this->testFile);

		$this->assertEquals('TSCP Example Policy', $policy->getName());
		$this->assertEquals('None', $policy->getId());
		$this->assertEquals('IntellectualProperty', $policy->getType());
		$this->assertEquals('TSCP Example Policy Authority', $policy->getAuthorityName());
		$this->assertEquals('None', $policy->getAuthorityId());
		$this->assertEquals('', $policy->getAuthorityCountry());
		$this->assertEquals('None', $policy->getAuthorizationName());
		$this->assertEquals('urn:example:tscp:1', $policy->getAuthorizationId());
		$this->assertEquals(new DateTime("2022-11-16T15:54:17"), $policy->getStartValidityDate());
		$this->assertEquals(null, $policy->getEndValidityDate());
		$this->assertEquals('3', $policy->getConfidentialityImpact());
		$this->assertEquals('3', $policy->getIntegrityImpact());
		$this->assertEquals('3', $policy->getAvailabilityImpact());
		$this->assertEquals('UK-Cabinet', $policy->getImpactScale());
		$this->assertCount(1, $policy->getCategories());
		$this->assertEquals('Internal Only', $policy->getCategories()[0]->getName());
		$this->assertEquals("urn:example:tscp:1:internal-only", $policy->getCategories()[0]->getId());
		$this->assertEquals("None", $policy->getCategories()[0]->getIdOID());
	}

	private function loginAndGetUserFolder(string $userId) {
		$this->loginAsUser($userId);
		return $this->rootFolder->getUserFolder($userId);
	}
}
