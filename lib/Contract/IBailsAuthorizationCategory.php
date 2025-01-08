<?php

/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Files_Confidential\Contract;

interface IBailsAuthorizationCategory {
	public function getName(): string;
	public function getId(): string;
	public function getIdOID(): string;
}
