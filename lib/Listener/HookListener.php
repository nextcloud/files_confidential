<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Files_Confidential\Listener;

use OCA\Files_Confidential\AuditLog\AuditLogger;
use OCA\Files_Confidential\AuditLog\Operation;
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
		private AuditLogger $auditLogger,
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
					$knownAppliedTags = array_values(array_intersect($classificationTags, $fileTags)); // Get all tags from file that files_confidential manages
					$previousTags = $knownAppliedTags;

					// Find the tag that the file should be assigned to based on the classification policy
					$label = $this->classificationService->getClassificationLabelForFile($node);
					$assignedTag = $label?->getTag();

					if ($assignedTag !== null) {
						$this->tagMapper->assignTags($nodeId, 'files', [$assignedTag]);
						$knownAppliedTags = array_values(array_diff($knownAppliedTags, [$assignedTag])); // Remove the tag that the file should be assigned to from the list of unnecessary tags
					}

					$this->tagMapper->unassignTags($nodeId, 'files', $knownAppliedTags);

					$isNewAssignment = $assignedTag !== null && !in_array($assignedTag, $previousTags, true);
					$removedOtherTags = $knownAppliedTags !== [];
					if ($isNewAssignment || $removedOtherTags || ($assignedTag === null && $previousTags !== [])) {
						if ($assignedTag !== null) {
							$this->auditLogger->log(Operation::FileClassified, 'File %s was classified with tag %s', [
								'fileId' => $nodeId,
								'tagId' => $assignedTag,
								'previousTags' => implode(',', $previousTags),
							]);
						} else {
							$this->auditLogger->log(Operation::FileClassified, 'Classification was removed from file %s', [
								'fileId' => $nodeId,
								'previousTags' => implode(',', $previousTags),
							]);
						}
					}
				} catch (\Throwable $e) {
					$this->logger->error('Failed to tag during NodeWrittenEvent', ['exception' => $e]);
				}
			}
		}
	}
}
