<?php

namespace OCA\Files_Confidential\Listener;

use OCA\Files_Confidential\Service\ClassificationService;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Files\Events\Node\NodeWrittenEvent;
use OCP\Files\File;
use OCP\SystemTag\ISystemTagObjectMapper;

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
		if ($event instanceof NodeWrittenEvent && $event->getNode() instanceof File) {
			$label = $this->classificationService->getClassificationLabelForFile($event->getNode());
			if ($label === null) {
				return;
			}
			$this->tagMapper->assignTags($event->getNode()->getId(), 'file', [$label->getTag()]);
		}
	}
}