<?php

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

		$this->assertEquals('Non-Business', $labels[0]->getName());
		$this->assertEquals('Non-Business', $labels[0]->getKeywords()[0]);
		$this->assertEquals('urn:example:tscp:1:non-business', $labels[0]->getBailsCategories()[0]);

		$this->assertEquals('General Business', $labels[1]->getName());
		$this->assertEquals('General Business', $labels[1]->getKeywords()[0]);
		$this->assertEquals('urn:example:tscp:1:general-business', $labels[1]->getBailsCategories()[0]);

		$this->assertEquals('Confidential', $labels[2]->getName());
		$this->assertEquals('Confidential', $labels[2]->getKeywords()[0]);
		$this->assertEquals('urn:example:tscp:1:confidential', $labels[2]->getBailsCategories()[0]);

		$this->assertEquals('Internal Only', $labels[3]->getName());
		$this->assertEquals('Internal Only', $labels[3]->getKeywords()[0]);
		$this->assertEquals('urn:example:tscp:1:internal-only', $labels[3]->getBailsCategories()[0]);
	}
}
