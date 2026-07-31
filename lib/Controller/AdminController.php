<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Files_Confidential\Controller;

use OCA\Files_Confidential\Service\BafService;
use OCA\Files_Confidential\Service\SettingsService;
use OCA\Files_Confidential\Settings\AdminSettings;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\AuthorizedAdminSetting;
use OCP\AppFramework\Http\JSONResponse;
use OCP\Files\IAppData;
use OCP\Files\NotFoundException;
use OCP\IL10N;
use OCP\IRequest;
use OCP\SystemTag\ISystemTagManager;
use OCP\SystemTag\TagAlreadyExistsException;
use Safe\Exceptions\JsonException;

class AdminController extends Controller {

	public function __construct(
		string $appName,
		IRequest $request,
		private SettingsService $settingsService,
		private IL10N $l10n,
		private BafService $bafService,
		private IAppData $appData,
		private ISystemTagManager $systemTagManager,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * @param array $value
	 * @return \OCP\AppFramework\Http\JSONResponse
	 */
	#[AuthorizedAdminSetting(settings: AdminSettings::class)]
	public function setClassificationLabels(array $value): JSONResponse {
		try {
			$this->settingsService->setClassificationLabels($value);
			return new JSONResponse([], Http::STATUS_OK);
		} catch (\Exception $e) {
			return new JSONResponse([], Http::STATUS_BAD_REQUEST);
		}
	}

	#[AuthorizedAdminSetting(settings: AdminSettings::class)]
	public function getClassificationLabels(): JSONResponse {
		$labels = $this->settingsService->getClassificationLabels();
		$labels = array_map(static fn ($label):array => $label->toArray(), $labels);
		return new JSONResponse($labels);
	}

	/**
	 * @return \OCP\AppFramework\Http\JSONResponse
	 * @throws \OCP\Files\NotPermittedException
	 */
	#[AuthorizedAdminSetting(settings: AdminSettings::class)]
	public function importBaf() : JSONResponse {
		$upload = $this->request->getUploadedFile('baf');
		$result = [];
		if ($upload === null || $upload['type'] !== 'text/xml') {
			$result['errors'][] = $this->l10n->t('Unsupported file type for import. An XML file is expected.');
			return new JSONResponse(['status' => 'error', 'data' => $result['errors']]);
		}

		$xml = file_get_contents($upload['tmp_name']);
		if ($xml === false) {
			$result['errors'][] = $this->l10n->t('Could not read uploaded file');
			return new JSONResponse(['status' => 'error', 'data' => $result['errors']]);
		}

		$labels = $this->bafService->parseXml($xml);
		try {
			$this->settingsService->setClassificationLabels(array_map(static fn ($label):array => $label->toArray(), $labels));
		} catch (JsonException $e) {
			$result['errors'][] = $this->l10n->t('Could not store extracted labels');
			return new JSONResponse(['status' => 'error', 'data' => $result['errors']]);
		}

		try {
			$folder = $this->appData->getFolder('files_confidential');
		} catch (NotFoundException $e) {
			$folder = $this->appData->newFolder('files_confidential');
		}
		$folder->newFile('baf.xml', $xml);

		return new JSONResponse([]);
	}

	/**
	 * Create a new restricted tag (visible but not user-assignable)
	 *
	 * @param string $name
	 * @return JSONResponse
	 */
	#[AuthorizedAdminSetting(settings: AdminSettings::class)]
	public function createTag(string $name): JSONResponse {
		$name = trim($name);
		if ($name === '') {
			return new JSONResponse(
				['error' => $this->l10n->t('Tag name cannot be empty')],
				Http::STATUS_BAD_REQUEST
			);
		}

		try {
			$tag = $this->systemTagManager->createTag($name, true, false);
			return new JSONResponse([
				'id' => $tag->getId(),
				'name' => $tag->getName(),
				'userVisible' => $tag->isUserVisible(),
				'userAssignable' => $tag->isUserAssignable(),
			], Http::STATUS_CREATED);
		} catch (TagAlreadyExistsException $e) {
			return new JSONResponse(
				['error' => $this->l10n->t('Tag "%s" already exists', [$name])],
				Http::STATUS_CONFLICT
			);
		} catch (\Exception $e) {
			return new JSONResponse(
				['error' => $this->l10n->t('Failed to create tag')],
				Http::STATUS_INTERNAL_SERVER_ERROR
			);
		}
	}
}
