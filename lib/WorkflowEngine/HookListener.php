<?php

namespace OCA\Files_Confidential\WorkflowEngine;

use OCA\Files_Confidential\Service\SettingsService;
use OCA\Files_Confidential\WorkflowEngine\Check\ClassificationLabelCheck;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IInitialStateService;
use OCP\Util;
use OCP\WorkflowEngine\Events\RegisterChecksEvent;
use OCP\WorkflowEngine\ICheck;

class HookListener implements IEventListener {
	private ICheck $check;
	private IInitialStateService $initialState;
	private SettingsService $settings;

	public function __construct(ClassificationLabelCheck $check, IInitialStateService $initialState, SettingsService $settings) {
		$this->check = $check;
		$this->initialState = $initialState;
		$this->settings = $settings;
	}

	public function handle(Event $event): void {
		if ($event instanceof RegisterChecksEvent) {
			$event->registerCheck($this->check);
			Util::addScript('files_confidential', 'files_confidential-flow');

			$labels = $this->settings->getClassificationLabels();
			$labels = array_map(fn ($label) => $label->toArray(), $labels);
			$this->initialState->provideInitialState('files_confidential', 'labels', $labels);
		}
	}
}
