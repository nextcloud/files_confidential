<?php

namespace OCA\Files_Confidential\Contract;

use DateTime;

interface IBailsPolicy {
	public function getName(): ?string;

	public function getId(): ?string;

	public function getType(): string;

	public function getAuthorityName(): string;

	public function getAuthorityId(): string;
	public function getAuthorityCountry(): string;

	public function getAuthorizationName(): string;
	public function getAuthorizationId():string;

	public function getStartValidityDate(): ?DateTime;
	public function getEndValidityDate(): ?DateTime;

	public function getConfidentialityImpact():string;
	public function getIntegrityImpact():string;
	public function getAvailabilityImpact():string;
	public function getImpactScale():string;

	/**
	 * @return list<IBailsAuthorizationCategory>
	 */
	public function getCategories():array;
}
