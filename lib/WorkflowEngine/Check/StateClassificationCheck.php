<?php

class StateClassificationCheck implements \OCP\WorkflowEngine\IFileCheck
{

	/**
	 * @inheritDoc
	 */
	public function executeCheck($operator, $value)
	{
		// TODO: Implement executeCheck() method.
	}

	/**
	 * @inheritDoc
	 */
	public function validateCheck($operator, $value)
	{
		// TODO: Implement validateCheck() method.
	}

	/**
	 * @inheritDoc
	 */
	public function supportedEntities(): array {
		return [ OCA\WorkflowEngine\Entity\File::class ];
	}

	/**
	 * @inheritDoc
	 */
	public function isAvailableForScope(int $scope): bool
	{
		return true;
	}

	public function setEntitySubject(\OCP\WorkflowEngine\IEntity $entity, $subject): void
	{
		// TODO: Implement setEntitySubject() method.
	}

	public function setFileInfo(\OCP\Files\Storage\IStorage $storage, string $path, bool $isDir = false): void
	{
		// TODO: Implement setFileInfo() method.
	}
}
