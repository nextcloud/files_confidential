<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Files_Confidential\AppInfo;

use OCA\Files_Confidential\Listener\HookListener;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\Files\Events\Node\NodeWrittenEvent;

class Application extends App implements IBootstrap {
	public const APP_ID = 'files_confidential';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}

	#[\Override]
	public function register(IRegistrationContext $context): void {
		@include_once __DIR__ . '/../../vendor/autoload.php';
		$context->registerEventListener(NodeWrittenEvent::class, HookListener::class);
	}

	/**
	 * @throws \Throwable
	 */
	#[\Override]
	public function boot(IBootContext $context): void {
	}
}
