<?php

/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

use Test\TestCase;

/**
 * @group DB
 */
class BafServiceTest extends TestCase {
	/**
	 * @return void
	 * @throws \Psr\Container\ContainerExceptionInterface
	 * @throws \Psr\Container\NotFoundExceptionInterface
	 */
	public function testParser() : void {
		$xml = file_get_contents(__DIR__ . '/res/example.xml');
		/** @var \OCA\Files_Confidential\Service\BafService $service */
		$service = \OC::$server->get(\OCA\Files_Confidential\Service\BafService::class);
		$labels = $service->parseXml($xml);

		$this->assertCount(4, $labels);

		$this->assertEquals('urn:example:tscp:1:non-business', $labels[0]->getBailsCategories()[0]);

		$this->assertEquals('urn:example:tscp:1:general-business', $labels[1]->getBailsCategories()[0]);

		$this->assertEquals('urn:example:tscp:1:confidential', $labels[2]->getBailsCategories()[0]);

		$this->assertEquals('urn:example:tscp:1:internal-only', $labels[3]->getBailsCategories()[0]);
	}
}
