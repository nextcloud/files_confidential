/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { createApp } from 'vue'
import { translate as t, translatePlural as n } from '@nextcloud/l10n'
import App from './views/Admin.vue'
import '@nextcloud/dialogs/style.css'

const AppInstance = createApp(App)
AppInstance.mixin({
	methods: { t, n },
	computed: {
		colorPrimary() {
			return getComputedStyle(document.documentElement).getPropertyValue('--color-primary')
		},
		colorPrimaryLight() {
			return getComputedStyle(document.documentElement).getPropertyValue('--color-primary-light')
		},
		colorPrimaryElement() {
			return getComputedStyle(document.documentElement).getPropertyValue('--color-primary-element')
		},
		colorPrimaryElementLight() {
			return getComputedStyle(document.documentElement).getPropertyValue('--color-primary-element-light')
		},
		colorPrimaryText() {
			return getComputedStyle(document.documentElement).getPropertyValue('--color-primary-text')
		},
		colorMainText() {
			return getComputedStyle(document.documentElement).getPropertyValue('--color-main-text')
		},
		colorMainBackground() {
			return getComputedStyle(document.documentElement).getPropertyValue('--color-main-background')
		},
		colorPlaceholderDark() {
			return getComputedStyle(document.documentElement).getPropertyValue('--color-placeholder-dark')
		},
	},
})
global.FilesConfidential = AppInstance.mount('#files_confidential')
