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
					$nodeId = (string)$node->getId();
					/** @var array<string, list<string>> $fileTags */
					$fileTags = $this->tagMapper->getTagIdsForObjects($nodeId, 'files')[$nodeId] ?? [];
					$knownAppliedTags = array_intersect($classificationTags, $fileTags); // Get all tags from file that files_confidential manages

					// Find all matching labels for the file based on classification rules
					$matchedLabels = $this->classificationService->getClassificationLabelsForFile($node);

					if (!empty($matchedLabels)) {
						$matchedTags = array_map(static fn ($label) => $label->getTag(), $matchedLabels);
						$this->tagMapper->assignTags($nodeId, 'files', $matchedTags);
						$knownAppliedTags = array_diff($knownAppliedTags, $matchedTags);
					}

					$this->tagMapper->unassignTags($nodeId, 'files', array_values($knownAppliedTags));
				} catch (\Throwable $e) {
					$this->logger->error('Failed to tag during NodeWrittenEvent', ['exception' => $e]);
				}
			}
		}
	}
}
