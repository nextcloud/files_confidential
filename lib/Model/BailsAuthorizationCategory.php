<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Files_Confidential\Model;

use OCA\Files_Confidential\Contract\IBailsAuthorizationCategory;

class BailsAuthorizationCategory implements IBailsAuthorizationCategory {
	private string $name = '';
	private string $id = '';
	private string $idOID = '';

	/**
	 * @return string
	 */
	#[\Override]
	public function getName(): string {
		return $this->name;
	}

	/**
	 * @param string $name
	 * @return BailsAuthorizationCategory
	 */
	public function setName(string $name): BailsAuthorizationCategory {
		$this->name = $name;
		return $this;
	}

	/**
	 * @return string
	 */
	#[\Override]
	public function getId(): string {
		return $this->id;
	}

	/**
	 * @param string $id
	 * @return BailsAuthorizationCategory
	 */
	public function setId(string $id): BailsAuthorizationCategory {
		$this->id = $id;
		return $this;
	}

	/**
	 * @return string
	 */
	#[\Override]
	public function getIdOID(): string {
		return $this->idOID;
	}

	/**
	 * @param string $idOID
	 * @return BailsAuthorizationCategory
	 */
	public function setIdOID(string $idOID): BailsAuthorizationCategory {
		$this->idOID = $idOID;
		return $this;
	}
}
