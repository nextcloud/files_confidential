<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Files_Confidential\Listener;

use OCA\Files_Confidential\Service\ClassificationService;
use OCA\Files_Confidential\Service\SettingsService;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Files\Events\Node\NodeWrittenEvent;
use OCP\Files\File;
use OCP\SystemTag\ISystemTagObjectMapper;
use Psr\Log\LoggerInterface;

/**
 * @implements IEventListener<Event>
 */
class HookListener implements IEventListener {
	public function __construct(
		private ClassificationService $classificationService,
		private ISystemTagObjectMapper $tagMapper,
		private LoggerInterface $logger,
		private SettingsService $settingsService,
	) {
	}

	/**
	 * @inheritDoc
	 */
	#[\Override]
	public function handle(Event $event): void {
		if ($event instanceof NodeWrittenEvent) {
			$node = $event->getNode();
			if ($node instanceof File) {
				try {
					// Find all tags that files confidential manages on this file
					$classificationTags = $this->settingsService->getTags();
					$fileTags = $this->tagMapper->getTagIdsForObjects((string)$node->getId(), 'files')[$node->getId()] ?? [];
					$knownAppliedTags = array_intersect($classificationTags, $fileTags); // Get all tags from file that files_confidential manages

					// Find the tag that the file should be assigned to based on the classification policy
					$label = $this->classificationService->getClassificationLabelForFile($node);

					if ($label !== null) {
						$this->tagMapper->assignTags((string)$event->getNode()->getId(), 'files', [$label->getTag()]);
						$knownAppliedTags = array_diff($knownAppliedTags, [$label->getTag()]); // Remove the tag that the file should be assigned to from the list of unnecessary tags
					}

					$this->tagMapper->unassignTags((string)$event->getNode()->getId(), 'files', $knownAppliedTags);
				} catch (\Throwable $e) {
					$this->logger->error('Failed to tag during NodeWrittenEvent', ['exception' => $e]);
				}
			}
		}
	}
}
