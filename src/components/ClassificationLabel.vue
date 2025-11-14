<!--
  - SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->
<template>
	<div class="label">
		<NcButton type="tertiary-no-background"
			class="close"
			:aria-label="t('files_confidential', 'Remove rule')"
			:title="t('files_confidential', 'Remove rule')"
			@click="$emit('remove', label.index)">
			<template #icon>
				<CloseIcon />
			</template>
		</NcButton>
		<label>
			<span class="text">{{ t('files_confidential', 'Add tag…') }}</span>
			<NcSelect v-model="label.tag"
				:options="tags"
				:label="'display-name'"
				:multiple="false"
				:label-outside="true"
				:filter-by="(option, label, search) => {
					return option['display-name'].toString().toLowerCase().includes(search.toLowerCase())
				}"
				:limit="5"
				:placeholder="t('files_confidential', 'Select tag')"
				@input="$emit('change')" />
		</label>
		<div class="options">
			<div class="option">
				<label>
					<span class="text">{{ t('files_confidential', '… if document has TSCP policy category ID') }}</span>
					<NcSelect v-model="label.categories"
						multiple
						taggable
						no-drop
						select-on-tab
						push-tags
						:label-outside="true"
						@input="$emit('change')" />
				</label>
			</div>
			<div class="option metadata">
				<label>
					<span class="text">{{ t('files_confidential', '… or if document has all metadata values') }}</span>
					<div v-for="(item, key) in label.metadataItems" :key="key">
						<NcTextField v-model="item.key"
							class="field"
							:title="item.key"
							:label="t('files_confidential', 'Metadata key')"
							@update:model-value="$emit('change')" />
						<NcTextField v-model="item.value"
							class="field"
							:title="item.value"
							:label="t('files_confidential', 'Metadata value')"
							@update:model-value="$emit('change')" />
					</div>
					<NcButton class="field" style="margin: 0 5px;" @click="addMetadataItem()">{{ t('files_confidential', 'Add') }}</NcButton>
				</label>
			</div>
			<div class="option data">
				<label>
					<span class="text">{{ t('files_confidential', '… or if document contains') }}</span>
					<div class="text">
						<div style="display: flex; flex-direction: row">
							<NcSelect v-model="input"
								label-visible
								taggable
								select-on-tab
								:options="Object.keys(searchExpressions)"
								:label-outside="true"
								:placeholder="t('files_confidential', 'Enter Regular Expression')"
								@input="$emit('change')">
								<template #option="{label: option}">
									<span><strong>{{ option }}</strong></span><br>
									<small><i>/{{ searchExpressions[option]||option }}/</i></small>
								</template>
							</NcSelect>
							<NcButton style="margin: 0 5px;" @click="addExpression()">
								{{ t('files_confidential', 'Add') }}
							</NcButton>
						</div>
					</div>
				</label>
			</div>
			<div class="option regex">
				<ul :aria-label="t('files_confidential', 'Search expressions within documents')">
					<NcListItem v-for="(exp, i) in label.searchExpressions"
						:key="exp"
						:name="exp"
						:compact="true"
						:force-display-actions="true">
						<template #subname>
							<span :title="searchExpressions[exp]">{{ searchExpressions[exp] }}</span>
						</template>
						<template #actions>
							<NcActionButton type="tertiary-no-background"
								:aria-label="t('files_confidential', 'Remove search expression')"
								@click="label.searchExpressions.splice(i,1); $emit('change')">
								<template #icon>
									<TrashCan :size="20" />
								</template>
							</NcActionButton>
						</template>
					</NcListItem>
					<NcListItem v-for="(regex, i) in label.regularExpressions"
						:key="regex"
						:name="regex"
						:compact="true"
						:force-display-actions="true">
						<template #subname>
							{{ t('files_confidential', 'Regular search expression') }}
						</template>
						<template #actions>
							<NcActionButton type="tertiary-no-background"
								:aria-label="t('files_confidential', 'Remove regular expression')"
								@click="label.regularExpressions.splice(i,1); $emit('change')">
								<template #icon>
									<TrashCan :size="20" />
								</template>
							</NcActionButton>
						</template>
					</NcListItem>
				</ul>
			</div>
			<div v-if="hasKeywords" class="option keywords">
				<ul :aria-label="t('files_confidential', 'Search keywords within documents')">
					<NcListItem v-for="keyword of label.keywords"
						:key="keyword"
						:name="keyword"
						:compact="true"
						:force-display-actions="true">
						<template #subname>
							{{ t('files_confidential', 'Search keyword') }}
						</template>
						<template #actions>
							<NcActionButton type="tertiary-no-background"
								:aria-label="t('files_confidential', 'Remove search keyword')"
								@click="onRemoveKeyword(keyword)">
								<template #icon>
									<TrashCan :size="20" />
								</template>
							</NcActionButton>
						</template>
					</NcListItem>
				</ul>
			</div>
		</div>
	</div>
</template>

<script>
import NcSelect from '@nextcloud/vue/components/NcSelect'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcListItem from '@nextcloud/vue/components/NcListItem'
import NcActionButton from '@nextcloud/vue/components/NcActionButton'
import NcTextField from '@nextcloud/vue/components/NcTextField'
import CloseIcon from 'vue-material-design-icons/Close.vue'
import TrashCan from 'vue-material-design-icons/TrashCanOutline.vue'

export default {
	name: 'ClassificationLabel',
	components: {
		NcSelect,
		NcButton,
		NcListItem,
		NcActionButton,
		CloseIcon,
		TrashCan,
		NcTextField,
	},
	props: {
		label: {
			type: Object,
			required: true,
		},
		tags: {
			type: Array,
			required: true,
		},
		searchExpressions: {
			type: Object,
			required: true,
		},
	},

	data() {
		return {
			input: '',
		}
	},

	computed: {
		hasKeywords() {
			return this.label.keywords && this.label.keywords.length > 0
		},
	},

	watch: {
		metadataKey() {
			if (!this.metadataKey) {
				this.label.metadataItems = []
				this.$emit('change')
				return
			}
			this.label.metadataItems = [{ key: this.metadataKey, value: this.metadataValue }]
			this.$emit('change')
		},
		metadataValue() {
			if (!this.metadataKey) {
				this.label.metadataItems = []
				this.$emit('change')
				return
			}
			this.label.metadataItems = [{ key: this.metadataKey, value: this.metadataValue }]
			this.$emit('change')
		},
	},
	methods: {
		addExpression(val) {
			if (this.searchExpressions[this.input]) {
				this.label.searchExpressions.push(this.input)
			} else {
				this.label.regularExpressions.push(this.input)
			}
			this.input = ''
			this.$emit('change')
		},
		addMetadataItem() {
			this.label.metadataItems.push({ key: '', value: '' })
		},

		/**
		 * Remove a keyword from the label
		 * @param {string} keyword Keyword to remove
		 */
		onRemoveKeyword(keyword) {
			this.label.keywords = this.label.keywords.filter((text) => text !== keyword)
			this.$emit('change')
		},
	},
}
</script>

<style scoped>
.option.regex,
.option.keywords,
.option.metadata .field {
	position: relative;
	left: 353px;
	width: 42% !important;
}

.option.regex li,
.option.keywords li {
	width: 400px;
	margin-bottom: 10px;
	display: flex;
	flex-direction: row;
	justify-content: space-between;
}

.option.regex li button {
	margin-top: -10px;
}
</style>
