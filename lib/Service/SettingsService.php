<?php

namespace OCA\Files_Confidential\Service;

use OCA\Files_Confidential\Model\ClassificationLabel;
use OCP\IConfig;
use Psr\Log\LoggerInterface;
use Safe\Exceptions\JsonException;

class SettingsService
{
	private string $appName;
	private IConfig $config;
	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	private LoggerInterface $logger;

	public function __construct($appName, IConfig $config, LoggerInterface $logger)
	{
		$this->appName = $appName;
		$this->config = $config;
		$this->logger = $logger;
	}

	/**
	 * @return list<\OCA\Files_Confidential\Contract\IClassificationLabel>
	 */
	public function getClassificationLabels():array {
		try {
			$labelsRaw = \Safe\json_decode($this->config->getAppValue($this->appName, 'labels', '[]'), true);
		} catch (JsonException $e) {
			$this->logger->warning('Could not load labels setting', ['exception' => $e]);
			return [];
		}
		try {
			return array_map(fn($labelRaw) => ClassificationLabel::fromArray($labelRaw), $labelsRaw);
		}catch(\ValueError $e) {
			$this->logger->warning('Could not load labels setting', ['exception' => $e]);
			return [];
		}
	}
}
