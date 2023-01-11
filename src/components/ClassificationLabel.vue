<template>
	<div :class="{'label':true}">
		<NcButton type="tertiary-no-background"
			class="close"
			:aria-label="t('files_confidential', 'Remove label')"
			@click="$emit('remove', label.index)">
			<template #icon>
				<CloseIcon />
			</template>
		</NcButton>
		<NcButton type="tertiary-no-background"
			class="up"
			:aria-label="t('files_confidential', 'Move label up')"
			@click="$emit('move-up',label.index)">
			<template #icon>
				<ArrowUpIcon />
			</template>
		</NcButton>
		<NcButton type="tertiary-no-background"
			class="down"
			:aria-label="t('files_confidential', 'Move label down')"
			@click="$emit('move-down', label.index)">
			<template #icon>
				<ArrowDownIcon />
			</template>
		</NcButton>
		<label>
			<div class="text"><TagIcon /> {{ t('files_confidential', 'Tag') }}</div>
			<NcSelect v-model="label.tag"
				:options="tags"
				:label="'display-name'"
				:multiple="false"
				:placeholder="t('files_confidential', 'Select tag')"
				@input="$emit('change')" />
		</label>
		<div class="options">
			<div class="option">
				<label>
					<div class="text"><PoundBoxIcon /> {{ t('files_confidential', 'TSCP policy category IDs') }}</div>
					<NcSelect v-model="label.categories"
						multiple
						taggable
						no-drop
						select-on-tab
						push-tags
						@input="$emit('change')" />
				</label>
			</div>
			<div class="option data">
				<label>
					<div class="text"><TextRecognitionIcon /> {{ t('files_confidential', 'Data to look for in document content') }}</div>
					<div :style="{display:'flex', flexDirection:'row'}">
						<NcSelect v-model="input"
							label-visible
							taggable
							select-on-tab
							:options="Object.keys(searchExpressions)"
							@input="$emit('change')">
							<template #option="{label: option}">
								<span>{{ option }}</span><br>
								<small><i>/{{ searchExpressions[option]||option }}/</i></small>
							</template>
						</NcSelect>
						<NcButton @click="addExpression()">Add</NcButton>
					</div>
				</label>
			</div>
			<div class="option regex">
				<ul>
					<li v-for="(exp,i) in label.searchExpressions" :key="exp">
						{{ exp }}<br><i>/{{ searchExpressions[exp] }}/</i>
						<NcButton type="tertiary-no-background"
							:aria-label="t('files_confidential', 'Remove search expression')"
							@click="label.searchExpressions.splice(i,1)">
							<template #icon>
								<CloseIcon />
							</template>
						</NcButton>
					</li>
					<li v-for="(regex,i) in label.regularExpressions" :key="regex">
						<i>/{{ regex }}/</i>
						<NcButton type="tertiary-no-background"
							:aria-label="t('files_confidential', 'Remove search expression')"
							@click="label.regularExpressions.splice(i,1)">
							<template #icon>
								<CloseIcon />
							</template>
						</NcButton>
					</li>
				</ul>
			</div>
		</div>
	</div>
</template>

<script>
import CloseIcon from 'vue-material-design-icons/Close.vue'
import ArrowUpIcon from 'vue-material-design-icons/ArrowUp.vue'
import ArrowDownIcon from 'vue-material-design-icons/ArrowDown.vue'
import TagIcon from 'vue-material-design-icons/Tag.vue'
import PoundBoxIcon from 'vue-material-design-icons/PoundBox.vue'
import TextRecognitionIcon from 'vue-material-design-icons/TextRecognition.vue'
import { NcSelect, NcButton } from '@nextcloud/vue'
export default {
	name: 'ClassificationLabel',
	components: {
		NcSelect,
		NcButton,
		CloseIcon,
		ArrowUpIcon,
		ArrowDownIcon,
		TagIcon,
		PoundBoxIcon,
		TextRecognitionIcon,
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
	},
}
</script>

<style scoped>
.option.regex li {
	margin-bottom: 10px;
	list-style-type: circle;
}

.option.regex li button {
	float: right;
	margin-top: -20px;
}
</style>
