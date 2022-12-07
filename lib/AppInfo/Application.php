<?php

/*
 * Copyright (c) 2021. The Nextcloud Recognize contributors.
 *
 * This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\Files_Confidential\AppInfo;

use OCA\Files_Confidential\WorkflowEngine\HookListener;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\WorkflowEngine\Events\RegisterChecksEvent;

class Application extends App implements IBootstrap {
	public const APP_ID = 'files_confidential';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}

	public function register(IRegistrationContext $context): void {
		@include_once __DIR__ . '/../../vendor/autoload.php';
		$context->registerEventListener(RegisterChecksEvent::class, HookListener::class);
	}

	/**
	 * @throws \Throwable
	 */
	public function boot(IBootContext $context): void {
	}
}
