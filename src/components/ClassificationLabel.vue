<template>
	<div class="label">
		<NcButton type="tertiary-no-background"
			class="close"
			:aria-label="t('files_confidential', 'Remove label')"
			@click="$emit('remove', label.index)">
			<template #icon>
				<CloseIcon />
			</template>
		</NcButton>
		<label>
			<span class="text">{{ t('files_confidential', 'Add tag …') }}</span>
			<NcSelect v-model="label.tag"
				:options="tags"
				:label="'display-name'"
				:multiple="false"
				:label-outside="true"
				:filter-by="(option, label, search) => {
					return option['display-name'].toLowerCase().includes(search.toLowerCase())
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
					<template v-for="(item, key) in label.metadataItems">
						<NcTextField :key="'key' + key"
							class="field"
							:value.sync="item.key"
							:title="item.key"
							label="Metadata key"
							@update:value="$emit('change')" />
						<NcTextField :key="'value' + key"
							class="field"
							:value.sync="item.value"
							:title="item.value"
							label="Metadata value"
							@update:value="$emit('change')" />
					</template>
					<NcButton class="field" style="margin: 0 5px;" @click="addMetadataItem()">{{ t('files_confidential', 'Add') }}</NcButton>
				</label>
			</div>
			<div class="option data">
				<label>
					<span class="text">{{ t('files_confidential', '… or if document contains') }}</span>
					<div class="text">
						<div :style="{display:'flex', flexDirection:'row'}">
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
							<NcButton style="margin: 0 5px;" @click="addExpression()">{{ t('files_confidential', 'Add') }}</NcButton>
						</div>
					</div>
				</label>
			</div>
			<div class="option regex">
				<ul>
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
									<TrashCan />
								</template>
							</NcActionButton>
						</template>
					</NcListItem>
					<NcListItem v-for="(regex, i) in label.regularExpressions"
						:key="regex"
						:name="regex"
						:compact="true"
						:force-display-actions="true">
						<template #actions>
							<NcActionButton type="tertiary-no-background"
								:aria-label="t('files_confidential', 'Remove search expression')"
								@click="label.regularExpressions.splice(i,1); $emit('change')">
								<template #icon>
									<TrashCan />
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
import NcSelect from '@nextcloud/vue/dist/Components/NcSelect.js'
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
import NcListItem from '@nextcloud/vue/dist/Components/NcListItem.js'
import NcActionButton from '@nextcloud/vue/dist/Components/NcActionButton.js'
import NcTextField from '@nextcloud/vue/dist/Components/NcTextField.js'
import CloseIcon from 'vue-material-design-icons/Close.vue'
import TrashCan from 'vue-material-design-icons/TrashCan.vue'

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
	},
}
</script>

<style scoped>
.option.regex {
	position: relative;
  left: 353px;
}

.option.metadata .field {
  position: relative;
  left: 353px;
  width: 42% !important;
}

.option.regex li {
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
