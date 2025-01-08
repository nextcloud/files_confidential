<?php

/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

use OCA\Files_Confidential\Providers\MetadataProviders\MicrosoftOfficeMetadataProvider;
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
		$provider = \OC::$server->get(MicrosoftOfficeMetadataProvider::class);
		$metadataItems = $provider->getMetadataForFile($this->testFile);

		$array = array_map(fn ($item) => $item->toArray(), $metadataItems);
		self::assertEquals([
			['key' => 'MSIP_Label_ccc06605-f14e-4fbc-a387-089df7c90678_Enabled', 'value' => 'true'],
			['key' => 'MSIP_Label_ccc06605-f14e-4fbc-a387-089df7c90678_SetDate', 'value' => '2024-01-18T23:42:17Z'],
			['key' => 'MSIP_Label_ccc06605-f14e-4fbc-a387-089df7c90678_Method', 'value' => 'Privileged'],
			['key' => 'MSIP_Label_ccc06605-f14e-4fbc-a387-089df7c90678_Name', 'value' => 'External'],
			['key' => 'MSIP_Label_ccc06605-f14e-4fbc-a387-089df7c90678_SiteId', 'value' => 'f7080c1c-08b5-4faf-b777-b81777c3b7b4'],
			['key' => 'MSIP_Label_ccc06605-f14e-4fbc-a387-089df7c90678_ActionId', 'value' => 'e4e6929d-412a-4e2f-9407-f35b8d870bf5'],
			['key' => 'MSIP_Label_ccc06605-f14e-4fbc-a387-089df7c90678_ContentBits', 'value' => '3'],
		], $array);
	}

	private function loginAndGetUserFolder(string $userId) {
		$this->loginAsUser($userId);
		return $this->rootFolder->getUserFolder($userId);
	}
}
