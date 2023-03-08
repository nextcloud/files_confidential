<?php

namespace OCA\Files_Confidential\Listener;

use OCA\Files_Confidential\Service\ClassificationService;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Files\Events\Node\NodeWrittenEvent;
use OCP\Files\File;
use OCP\SystemTag\ISystemTagObjectMapper;
use Psr\Log\LoggerInterface;

class HookListener implements IEventListener {
	private ClassificationService $classificationService;

	private ISystemTagObjectMapper $tagMapper;

	public function __construct(ClassificationService $classificationService, ISystemTagObjectMapper $tagMapper) {
		$this->classificationService = $classificationService;
		$this->tagMapper = $tagMapper;
	}
	/**
	 * @inheritDoc
	 */
	public function handle(Event $event): void {
		$node = $event->getNode();
		if ($event instanceof NodeWrittenEvent && $node instanceof File) {
			try {
				$label = $this->classificationService->getClassificationLabelForFile($node);
				if ($label === null) {
					return;
				}
				$this->tagMapper->assignTags((string)$event->getNode()->getId(), 'files', [(int)$label->getTag()]);
			} catch (\Throwable $e) {
				\OCP\Server::get(LoggerInterface::class)->error('Failed to tag during NodeWrittenEvent', ['exception' => $e]);
			}
		}
	}
}
