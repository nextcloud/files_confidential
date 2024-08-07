<?php

declare(strict_types=1);

namespace OCA\Files_Confidential\Listener;

use OCA\Files_Confidential\Service\ClassificationService;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Files\Events\Node\NodeWrittenEvent;
use OCP\Files\File;
use OCP\Files\InvalidPathException;
use OCP\Files\NotFoundException;
use OCP\Files\Template\FileCreatedFromTemplateEvent;
use OCP\SystemTag\ISystemTagObjectMapper;
use Psr\Log\LoggerInterface;

/**
 * @implements IEventListener<Event>
 */
class HookListener implements IEventListener {
	public function __construct(
		private ClassificationService $classificationService,
		private ISystemTagObjectMapper $tagMapper,
		private LoggerInterface $logger
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function handle(Event $event): void {
		if ($event instanceof NodeWrittenEvent) {
			$node = $event->getNode();
			if ($node instanceof File) {
				try {
					$this->handleNodeTagging($node);
				} catch (\Throwable $e) {
					$this->logger->error('Failed to tag during NodeWrittenEvent', ['exception' => $e]);
				}
			}
		}

		if ($event instanceof FileCreatedFromTemplateEvent) {
			$template = $event->getTemplate();
			if ($template === null) {
				return;
			}
			$node = $event->getTarget();
			try {
				$this->handleNodeTagging($node);
			} catch (\Throwable $e) {
				$this->logger->error('Failed to tag during FileCreatedFromTemplateEvent', ['exception' => $e]);
			}
		}
	}

	/**
	 * @throws NotFoundException
	 * @throws InvalidPathException
	 */
	private function handleNodeTagging(File $node): void {
		$label = $this->classificationService->getClassificationLabelForFile($node);
		if ($label === null) {
			return;
		}
		$this->tagMapper->assignTags((string)$node->getId(), 'files', [(int)$label->getTag()]);
	}
}
