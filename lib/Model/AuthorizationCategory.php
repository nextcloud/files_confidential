<?php

namespace OCA\Files_Confidential\Model;

use OCA\Files_Confidential\Contract\IAuthorizationCategory;

class AuthorizationCategory implements IAuthorizationCategory {
	private string $name = '';
	private string $id = '';
	private string $idOID = '';

	/**
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * @param string $name
	 * @return AuthorizationCategory
	 */
	public function setName(string $name): AuthorizationCategory {
		$this->name = $name;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getId(): string {
		return $this->id;
	}

	/**
	 * @param string $id
	 * @return AuthorizationCategory
	 */
	public function setId(string $id): AuthorizationCategory {
		$this->id = $id;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getIdOID(): string {
		return $this->idOID;
	}

	/**
	 * @param string $idOID
	 * @return AuthorizationCategory
	 */
	public function setIdOID(string $idOID): AuthorizationCategory {
		$this->idOID = $idOID;
		return $this;
	}
}
