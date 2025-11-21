/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

module.exports = {
	extends: [
		'@nextcloud',
	],
	parserOptions: {
		requireConfigFile: false,
	},
	rules: {
		'n/no-unpublished-import': 'off',
		'n/no-process-exit': 'off',
		'no-console': 'off',
		'n/no-missing-require': 'off',
		'vue/no-mutating-props': 'off',
	},
}
