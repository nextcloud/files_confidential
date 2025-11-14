<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Files_Confidential\Model;

use OCA\Files_Confidential\Contract\IBailsAuthorizationCategory;
use OCA\Files_Confidential\Contract\IBailsPolicy;

class BailsPolicy implements IBailsPolicy {
	private string $name = '';
	private string $id = '';
	private string $type = '';
	private string $authorityName = '';
	private string $authorityId = '';
	private string $authorityCountry = '';
	private string $authorizationName = '';
	private string $authorizationId = '';
	private string $startValidityDate = '';
	private string $endValidityDate = '';
	private string $confidentialityImpact = '';
	private string $integrityImpact = '';
	private string $availabilityImpact = '';
	private string $impactScale = '';

	/**
	 * @var list<IBailsAuthorizationCategory>
	 */
	private array $categories = [];

	/**
	 * @param list<array{key:string, value:string}> $props
	 * @return BailsPolicy
	 */
	public static function fromBAILS(array $props): BailsPolicy {
		$policy = new self();
		$lastCategory = new BailsAuthorizationCategory();
		$policy->addCategory($lastCategory);

		foreach ($props as $prop) {
			if (stripos($prop['key'], 'urn:bails:') !== 0) {
				continue;
			}
			$keyParts = explode(':', $prop['key']);
			$policy->setType($keyParts[2]);
			$key = implode(':', array_slice($keyParts, 3));
			$value = $prop['value'];

			// we've matched all (urn:bails:*:)* now, and $key is everything after the closing paren
			switch ($key) {
				case 'Policy:Identifier':
					$policy->setId($value);
					break;
				case 'Policy:Name':
					$policy->setName($value);
					break;
				case 'PolicyAuthority:Identifier':
					$policy->setAuthorityId($value);
					break;
				case 'PolicyAuthority:Name':
					$policy->setAuthorityName($value);
					break;
				case 'Impact:Level:Availability':
					$policy->setAvailabilityImpact($value);
					break;
				case 'Impact:Level:Confidentiality':
					$policy->setConfidentialityImpact($value);
					break;
				case 'Impact:Level:Integrity':
					$policy->setIntegrityImpact($value);
					break;
				case 'Impact:Scale':
					$policy->setImpactScale($value);
					break;
				case 'BusinessAuthorization:Name':
					$policy->setAuthorizationName($value);
					break;
				case 'BusinessAuthorization:Identifier':
					$policy->setAuthorizationId($value);
					break;
				case 'Authorization:StartValidity':
					$policy->setStartValidityDate($value);
					break;
				case 'Authorization:StopValidity':
					$policy->setEndValidityDate($value);
					break;
				case 'BusinessAuthorizationCategory:Identifier':
					$lastCategory->setId($value);
					break;
				case 'BusinessAuthorizationCategory:Name':
					$lastCategory->setName($value);
					break;
				case 'BusinessAuthorizationCategory:Identifier:OID':
					$lastCategory->setIdOID($value);
					break;
			}
		}
		return $policy;
	}

	/**
	 * @return string
	 */
	#[\Override]
	public function getName(): string {
		return $this->name;
	}

	/**
	 * @param string $name
	 * @return BailsPolicy
	 */
	public function setName(string $name): BailsPolicy {
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
	 * @return BailsPolicy
	 */
	public function setId(string $id): BailsPolicy {
		$this->id = $id;
		return $this;
	}

	/**
	 * @return string
	 */
	#[\Override]
	public function getAuthorityName(): string {
		return $this->authorityName;
	}

	/**
	 * @param string $authorityName
	 * @return BailsPolicy
	 */
	public function setAuthorityName(string $authorityName): BailsPolicy {
		$this->authorityName = $authorityName;
		return $this;
	}

	/**
	 * @return string
	 */
	#[\Override]
	public function getAuthorityId(): string {
		return $this->authorityId;
	}

	/**
	 * @param string $authorityId
	 * @return BailsPolicy
	 */
	public function setAuthorityId(string $authorityId): BailsPolicy {
		$this->authorityId = $authorityId;
		return $this;
	}

	/**
	 * @return string
	 */
	#[\Override]
	public function getAuthorizationName(): string {
		return $this->authorizationName;
	}

	/**
	 * @param string $authorizationName
	 * @return BailsPolicy
	 */
	public function setAuthorizationName(string $authorizationName): BailsPolicy {
		$this->authorizationName = $authorizationName;
		return $this;
	}

	/**
	 * @return string
	 */
	#[\Override]
	public function getAuthorizationId(): string {
		return $this->authorizationId;
	}

	/**
	 * @param string $authorizationId
	 * @return BailsPolicy
	 */
	public function setAuthorizationId(string $authorizationId): BailsPolicy {
		$this->authorizationId = $authorizationId;
		return $this;
	}


	/**
	 * @return ?\DateTime
	 */
	#[\Override]
	public function getStartValidityDate(): ?\DateTime {
		try {
			return new \DateTime(explode(',', $this->startValidityDate)[0]);
		} catch (\Exception $e) {
			return null;
		}
	}

	/**
	 * @param string $startValidityDate
	 * @return BailsPolicy
	 */
	public function setStartValidityDate(string $startValidityDate): BailsPolicy {
		$this->startValidityDate = $startValidityDate;
		return $this;
	}

	/**
	 * @return ?\DateTime
	 */
	#[\Override]
	public function getEndValidityDate(): ?\DateTime {
		try {
			return new \DateTime(explode(',', $this->endValidityDate)[0]);
		} catch (\Exception $e) {
			return null;
		}
	}

	/**
	 * @param string $endValidityDate
	 * @return BailsPolicy
	 */
	public function setEndValidityDate(string $endValidityDate): BailsPolicy {
		$this->endValidityDate = $endValidityDate;
		return $this;
	}

	/**
	 * @return string
	 */
	#[\Override]
	public function getType(): string {
		return $this->type;
	}

	/**
	 * @param string $type
	 * @return BailsPolicy
	 */
	public function setType(string $type): BailsPolicy {
		$this->type = $type;
		return $this;
	}

	/**
	 * @return string
	 */
	#[\Override]
	public function getAuthorityCountry(): string {
		return $this->authorityCountry;
	}

	/**
	 * @param string $authorityCountry
	 * @return BailsPolicy
	 */
	public function setAuthorityCountry(string $authorityCountry): BailsPolicy {
		$this->authorityCountry = $authorityCountry;
		return $this;
	}

	/**
	 * @return string
	 */
	#[\Override]
	public function getConfidentialityImpact(): string {
		return $this->confidentialityImpact;
	}

	/**
	 * @param string $confidentialityImpact
	 * @return BailsPolicy
	 */
	public function setConfidentialityImpact(string $confidentialityImpact): BailsPolicy {
		$this->confidentialityImpact = $confidentialityImpact;
		return $this;
	}

	/**
	 * @return string
	 */
	#[\Override]
	public function getIntegrityImpact(): string {
		return $this->integrityImpact;
	}

	/**
	 * @param string $integrityImpact
	 * @return BailsPolicy
	 */
	public function setIntegrityImpact(string $integrityImpact): BailsPolicy {
		$this->integrityImpact = $integrityImpact;
		return $this;
	}

	/**
	 * @return string
	 */
	#[\Override]
	public function getAvailabilityImpact(): string {
		return $this->availabilityImpact;
	}

	/**
	 * @param string $availabilityImpact
	 * @return BailsPolicy
	 */
	public function setAvailabilityImpact(string $availabilityImpact): BailsPolicy {
		$this->availabilityImpact = $availabilityImpact;
		return $this;
	}

	/**
	 * @return string
	 */
	#[\Override]
	public function getImpactScale(): string {
		return $this->impactScale;
	}

	/**
	 * @param string $impactScale
	 * @return BailsPolicy
	 */
	public function setImpactScale(string $impactScale): BailsPolicy {
		$this->impactScale = $impactScale;
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	#[\Override]
	public function getCategories(): array {
		return $this->categories;
	}

	public function addCategory(IBailsAuthorizationCategory $category): BailsPolicy {
		$this->categories[] = $category;
		return $this;
	}
}
