<!--
  - SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->
<template>
	<div id="files_confidential">
		<NcLoadingIcon v-if="loading" class="loading-icon" />
		<CheckIcon v-if="!loading && success" class="success-icon" />
		<NcSettingsSection :name="t('files_confidential', 'Business Authorization Framework')"
			:description="t('files_confidential', 'Upload your TSCP/BAILS policy classification labels (XML format)')">
			<input ref="fileInput"
				type="file"
				name="baf"
				@change="onImportSubmit">
			<NcButton type="secondary"
				:aria-label="t('files_confidential', 'Upload policy')"
				@click="$refs.fileInput.click()">
				{{ t('files_confidential', 'Upload policy') }}
			</NcButton>
			<NcNoteCard type="info">
				{{ t('files_confidential', 'Previous labels will be overwritten after successful file upload') }}
			</NcNoteCard>
		</NcSettingsSection>
		<NcSettingsSection :name="t('files_confidential', 'Classification rules')"
			:description="t('files_confidential', 'Define classification rules that apply tags to different documents. Based on these tags you can define rules in Nextcloud Flow.')">
			<transition-group name="labels" tag="div">
				<ClassificationLabel v-for="label in labels"
					:key="label.id"
					:label="label"
					:tags="tags"
					:search-expressions="searchExpressions"
					@moveUp="moveUpLabel(label.index)"
					@moveDown="moveDownLabel(label.index)"
					@remove="removeLabel(label.index)"
					@change="onChange()" />
				<div :key="'--new--'" :class="{'label':true, 'collapsed': true, 'add': true}">
					<NcButton type="primary"
						class="add"
						:aria-label="t('files_confidential', 'Add new label')"
						@click="addLabel()">
						<template #icon>
							<PlusIcon />
						</template>
					</NcButton>
				</div>
			</transition-group>
		</NcSettingsSection>
	</div>
</template>

<script>

import PlusIcon from 'vue-material-design-icons/Plus.vue'
import CheckIcon from 'vue-material-design-icons/Check.vue'
import NcSettingsSection from '@nextcloud/vue/components/NcSettingsSection'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcLoadingIcon from '@nextcloud/vue/components/NcLoadingIcon'
import NcNoteCard from '@nextcloud/vue/components/NcNoteCard'
import { loadState } from '@nextcloud/initial-state'
import { generateUrl } from '@nextcloud/router'
import { showError, showSuccess, showWarning } from '@nextcloud/dialogs'
import axios from '@nextcloud/axios'
import ClassificationLabel from '../components/ClassificationLabel.vue'
import client from '../DavClient.js'

const SETTINGS = ['labels']

export default {
	name: 'Admin',
	components: {
		NcSettingsSection,
		NcButton,
		PlusIcon,
		NcLoadingIcon,
		NcNoteCard,
		CheckIcon,
		ClassificationLabel,
	},

	data() {
		return {
			loading: false,
			loadingLabels: false,
			success: false,
			error: '',
			timeout: null,
			labels: [],
			tags: [],
			searchExpressions: [],
		}
	},

	watch: {
		error(error) {
			if (!error) return
			showError(error)
		},
	},

	async created() {
		await this.initTagsAndLabels()
	},

	methods: {
		removeLabel(index) {
			this.labels = this.labels
				.filter((label) => label.index !== index)
				.map((label, index) => ({ ...label, index }))
			this.onChange()
		},

		moveDownLabel(index) {
			if (index === this.labels.length - 1) return
			const labels = this.labels.splice(index, 1)
			this.labels.splice(index + 1, 0, ...labels)
			this.labels = this.labels.map((label, index) => ({ ...label, index }))
			this.onChange()
		},

		moveUpLabel(index) {
			if (index === 0) return
			const labels = this.labels.splice(index, 1)
			this.labels.splice(index - 1, 0, ...labels)
			this.labels = this.labels.map((label, index) => ({ ...label, index }))
			this.onChange()
		},

		toggle(index) {
			this.$set(this.labels, index, { ...this.labels[index], expanded: !this.labels[index].expanded })
		},

		addLabel() {
			if (this.labels.filter(label => !label.tag).length > 0) {
				showWarning(t('files_confidential', 'Can not add new label, until all labels have a tag assigned.'))
				return
			}
			this.labels.push({
				id: Math.random(),
				index: this.labels.length,
				tag: '',
				keywords: [],
				categories: [],
				searchExpressions: [],
				regularExpressions: [],
				metadataItems: [],
			})
		},

		onChange() {
			if (this.timeout) {
				clearTimeout(this.timeout)
			}
			this.timeout = setTimeout(() => {
				this.submit()
			}, 1000)
		},

		async submit() {
			this.loading = true
			for (const setting of SETTINGS) {
				await this.setValue(setting, this[setting])
			}
			this.loading = false
			this.success = true
			setTimeout(() => {
				this.success = false
			}, 3000)
		},

		async setValue(setting, value) {
			if (setting === 'labels') {
				value = value.map(label => ({
					...label, tag: String(label?.tag?.id) || '',
				}))
			}
			try {
				await axios.put(generateUrl(`/apps/files_confidential/admin/settings/${setting}`), {
					value,
				})
			} catch (e) {
				this.error = this.t('files_confidential', 'Failed to save settings')
				throw e
			}
		},

		async onImportSubmit(e) {
			const file = e.target.files[0]
			const data = new FormData()
			data.append('baf', file)
			this.loading = true
			this.error = ''
			let res
			try {
				({ data: res } = await axios.post(generateUrl('/apps/files_confidential/admin/baf'), data))
			} catch (e) {
				this.loading = false
				return
			}
			if (res.status === 'error') {
				this.error = res.data[0]
				this.loading = false
				return
			}
			this.loading = false
			this.success = true
			showSuccess(t('files_confidential', 'Policy uploaded successfully'))
			this.loadLabels()
			setTimeout(() => {
				this.success = false
			}, 3000)
		},
		async getTags() {
			const response = await client.getDirectoryContents('/systemtags/', Object.assign({}, {
				data: `<?xml version="1.0"?>
			<d:propfind  xmlns:d="DAV:"
				xmlns:oc="http://owncloud.org/ns">
				<d:prop>
					<oc:id />
					<oc:display-name />
					<oc:user-visible />
					<oc:user-assignable />
					<oc:can-assign />
				</d:prop>
			</d:propfind>`,
				details: true,
			}))
			console.error(response)

			return response.data.map(item => item.props).filter(item => item.id)
		},

		async initTagsAndLabels() {
			this.tags = await this.getTags()
			this.searchExpressions = loadState('files_confidential', 'searchExpressions')
			this.labels = loadState('files_confidential', 'labels')
				.map(label => ({ ...label, id: Math.random(), tag: this.tags.find(tag => String(tag.id) === String(label.tag)) }))
		},

		loadLabels() {
			axios.get(generateUrl('/apps/files_confidential/admin/settings/labels'))
				.then(res => {
					this.labels = res.data.map(label => ({ ...label, id: Math.random(), tag: this.tags.find(tag => String(tag.id) === String(label.tag)) }))
				})
		},
	},
}
</script>
<style>
figure[class^='icon-'] {
	display: inline-block;
}

#files_confidential {
	position: relative;
}

#files_confidential .indent {
	margin-left: 20px;
}

#files_confidential .loading-icon,
#files_confidential .success-icon {
	position: fixed;
	top: 70px;
	right: 20px;
}

#files_confidential .v-select input {
	border: none;
}

#files_confidential label .text {
	display: table-cell;
	margin-bottom: 5px;
	min-width: 350px;
}

#files_confidential .label {
	position: relative;
	box-shadow: 0 0 2px 0 var(--color-box-shadow);
	margin-bottom: 20px;
	border-radius: var(--border-radius-large);
	padding: 12px 60px;
}

#files_confidential .label > .options {
	display: flex;
	flex-direction: row;
	flex-wrap: wrap;
	margin-top: 20px;
}

#files_confidential .options > .option {
	margin-right: 20px;
	margin-top: 10px;
}

#files_confidential .options > .option.data,
#files_confidential .options > .option.metadata,
#files_confidential .options > .option.regex {
	width: 100%;
}

#files_confidential .label .close {
	position: absolute;
	right: 5px;
	top: 5px;
}

#files_confidential .label.add {
	display: flex;
	align-items: center;
	flex-direction: column;
}

input[type='file'] {
	display: none;
}

/*
 Fixes vue-select clear buttons to avoid them inheriting wrong button style
 */
.vs__deselect,
.vs__clear {
	min-height: auto !important;
	padding: 0 !important;
	margin: 0 !important;
	border: none !important;
}

/* animations */

.label {
	transition: all 1s;
}

.labels-move {
	transition: transform 1s;
}

.labels-active, .list-leave-active {
	transition: all 1s;
}

.labels-enter, .labels-leave-to {
	opacity: 0;
	transform: translateX(30px);
}
</style>
