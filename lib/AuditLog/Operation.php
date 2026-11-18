<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Files_Confidential\AuditLog;

/**
 * Operations Files Confidential writes to the admin audit log, in the form `app.entity.action`
 *
 * The values are matched by log consumers (filters, alert rules, SIEM imports),
 * so changing one is a breaking change for them. Case names may be renamed.
 */
enum Operation: string {
	case LabelsUpdated = 'files_confidential.labels.updated';
	case BafImported = 'files_confidential.baf.imported';
	case FileClassified = 'files_confidential.file.classified';

	/**
	 * Short description of when the operation is logged, used for the documentation
	 */
	public function description(): string {
		return match ($this) {
			self::LabelsUpdated => 'Classification labels were created, changed or deleted in admin settings',
			self::BafImported => 'A Business Authorization Framework (BAF) XML policy was imported',
			self::FileClassified => 'A classification tag was added to or changed on a file',
		};
	}
}
