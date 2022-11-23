<?php

namespace OCA\Files_Confidential\Contract;

interface IAuthorizationCategory {
	public function getName(): string;
	public function getId(): string;
	public function getIdOID(): string;
}
