<?php

namespace OCA\Files_Confidential;

use OCA\Files_Confidential\WorkflowEngine\Check\ClassificationLabelCheck;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\WorkflowEngine\Events\RegisterChecksEvent;
use OCP\WorkflowEngine\ICheck;

class HookListener implements IEventListener {
	private ICheck $check;

	public function __construct(ClassificationLabelCheck $check) {
		$this->check = $check;
	}

	public function handle(Event $event): void {
		if ($event instanceof RegisterChecksEvent) {
			$event->registerCheck($this->check);
		}
	}
}
