<!--
  - Copyright (c) 2021. The files_confidential contributors.
  -
  - This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
  -->

<template>
	<div id="files_confidential">
		<NcLoadingIcon v-if="loading" class="loading-icon" />
		<CheckIcon v-if="!loading && success" class="success-icon" />
		<NcSettingsSection :title="t('files_confidential', 'Business Authroization Framework')">
			<input ref="fileInput"
				type="file"
				name="baf"
				@change="onImportSubmit">
			<NcButton type="secondary"
				:aria-label="t('files_confidential', 'Upload policy')"
				@click="$refs.fileInput.click()">
				{{ t('files_confidential', 'Upload policy') }}
			</NcButton>
		</NcSettingsSection>
		<NcSettingsSection :title="t('files_confidential', 'Classification labels')">
			<p>{{ t('files_confidential', 'Define classification labels that apply to different documents. Based on these labels you can define rules in Nextcloud Flow.') }}</p>
			<p>&nbsp;</p>
			<transition-group name="labels" tag="div">
				<div v-for="label in labels" :key="label.id" :class="{'label':true, 'collapsed': !label.expanded}">
					<NcButton type="tertiary-no-background"
						class="close"
						:aria-label="t('files_confidential', 'Remove label')"
						@click="removeLabel(label.index)">
						<template #icon>
							<CloseIcon />
						</template>
					</NcButton>
					<NcButton type="tertiary-no-background"
						class="up"
						:aria-label="t('files_confidential', 'Move label up')"
						@click="moveUpLabel(label.index)">
						<template #icon>
							<ArrowUpIcon />
						</template>
					</NcButton>
					<NcButton type="tertiary-no-background"
						class="down"
						:aria-label="t('files_confidential', 'Move label down')"
						@click="moveDownLabel(label.index)">
						<template #icon>
							<ArrowDownIcon />
						</template>
					</NcButton>
					<NcTextField :value.sync="label.name" :label="t('files_confidential', 'Label name')" @update:value="onChange()">
						<LabelOutlineIcon />
					</NcTextField>
					<div class="option">
						<label>
							{{ t('files_confidential', 'TSCP policy category IDs') }}<br>
							<NcSelect v-model="label.categories"
								multiple
								taggable
								no-drop
								select-on-tab
								push-tags
								@input="onChange()" />
						</label>
					</div>
					<div class="option">
						<label>
							{{ t('files_confidential', 'Keywords to look for in files') }}<br>
							<NcSelect v-model="label.keywords"
								label-visible
								multiple
								taggable
								no-drop
								select-on-tab
								push-tags
								@input="onChange()" />
						</label>
					</div>
					<NcButton v-if="!label.expanded"
						type="tertiary"
						class="expand"
						wide
						:aria-label="t('files_confidential', 'Expand label options')"
						@click="toggle(label.index)">
						<template #icon>
							<ChevronDownIcon />
						</template>
					</NcButton>
					<NcButton v-else
						type="tertiary"
						class="collapse"
						wide
						:aria-label="t('files_confidential', 'Expand label options')"
						@click="toggle(label.index)">
						<template #icon>
							<ChevronUpIcon />
						</template>
					</NcButton>
				</div>
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
import LabelOutlineIcon from 'vue-material-design-icons/LabelOutline.vue'
import CloseIcon from 'vue-material-design-icons/Close.vue'
import ArrowUpIcon from 'vue-material-design-icons/ArrowUp.vue'
import ArrowDownIcon from 'vue-material-design-icons/ArrowDown.vue'
import ChevronDownIcon from 'vue-material-design-icons/ChevronDown.vue'
import ChevronUpIcon from 'vue-material-design-icons/ChevronUp.vue'
import PlusIcon from 'vue-material-design-icons/Plus.vue'
import CheckIcon from 'vue-material-design-icons/Check.vue'
import { NcSelect, NcSettingsSection, NcTextField, NcButton, NcLoadingIcon } from '@nextcloud/vue'
import { loadState } from '@nextcloud/initial-state'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'

const SETTINGS = ['labels']

export default {
	name: 'Admin',
	components: {
		NcSelect,
		NcSettingsSection,
		NcTextField,
		LabelOutlineIcon,
		NcButton,
		CloseIcon,
		ArrowUpIcon,
		ArrowDownIcon,
		ChevronDownIcon,
		ChevronUpIcon,
		PlusIcon,
		NcLoadingIcon,
		CheckIcon,
	},

	data() {
		return {
			loading: false,
			success: false,
			error: '',
			timeout: null,
			labels: [],
		}
	},

	watch: {
		error(error) {
			if (!error) return
			OC.Notification.showTemporary(error)
		},
	},
	async created() {
		this.labels = loadState('files_confidential', 'labels')
			.map(label => ({ ...label, id: Math.random() }))
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
			if (this.labels.filter(label => label.name.trim() === '').length > 0) {
				return
			}
			this.labels.push({
				id: Math.random(),
				index: this.labels.length,
				name: '',
				expanded: true,
				keywords: [],
				categories: [],
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
			try {
				await axios.put(generateUrl(`/apps/files_confidential/admin/settings/${setting}`), {
					value,
				})
			} catch (e) {
				this.error = this.t('recognize', 'Failed to save settings')
				throw e
			}
		},

		async onImportSubmit(e) {
			const file = e.target.files[0]
			const data = new FormData()
			data.append('baf', file)
			this.loading = true
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
			setTimeout(() => {
				this.success = false
			}, 3000)
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

#files_confidential .label {
	position: relative;
	background-color: var(--color-background-dark);
	margin-bottom: 20px;
	border-radius: var(--border-radius-large);
	padding: 12px 60px;
}

#files_confidential .label > .option {
	margin-top: 20px;
}

#files_confidential .label.collapsed > .option {
	display: none;
}

#files_confidential .label .close {
	position: absolute;
	right: 5px;
	top: 5px;
}

#files_confidential .label .expand,
#files_confidential .label .collapse {
	display: block;
	width: 100% !important;
}

#files_confidential .label .up {
	position: absolute;
	left: 5px;
	top:50px;
}

#files_confidential .label .down {
	position: absolute;
	left: 5px;
	bottom: 50px;
}

#files_confidential .label:nth-last-child(2) .down {
	display: none;
}

#files_confidential .label:first-child .up {
	display: none;
}

#files_confidential .label.collapsed .up {
	position: absolute;
	left: 5px;
	top: 5px;
}

#files_confidential .label.collapsed .down {
	position: absolute;
	left: 5px;
	bottom: 5px;
}

#files_confidential .label.add {
	display: flex;
	align-items: center;
	flex-direction: column;
}

input[type="file"] {
	display: none;
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
