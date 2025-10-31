<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Files_Confidential\Listener;

use OCA\Files_Confidential\Service\ClassificationService;
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
					$label = $this->classificationService->getClassificationLabelForFile($node);
					if ($label === null) {
						return;
					}
					$this->tagMapper->assignTags((string)$event->getNode()->getId(), 'files', [(int)$label->getTag()]);
				} catch (\Throwable $e) {
					$this->logger->error('Failed to tag during NodeWrittenEvent', ['exception' => $e]);
				}
			}
		}
	}
}
