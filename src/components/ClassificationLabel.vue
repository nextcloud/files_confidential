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
		<label>
			<div class="text">{{ t('files_confidential', 'Add tag ...') }}</div>
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
					<div class="text">{{ t('files_confidential', '... if document has TSCP policy category ID') }}</div>
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
					<div class="text">{{ t('files_confidential', '... if document contains') }}</div>
					<div class="text">
						<div :style="{display:'flex', flexDirection:'row'}">
							<NcSelect v-model="input"
								label-visible
								taggable
								select-on-tab
								:options="Object.keys(searchExpressions)"
								:placeholder="t('files_confidential', 'Enter Regular Expression')"
								@input="$emit('change')">
								<template #option="{label: option}">
									<span><strong>{{ option }}</strong></span><br>
									<small><i>/{{ searchExpressions[option]||option }}/</i></small>
								</template>
							</NcSelect>
							<NcButton @click="addExpression()">Add</NcButton>
						</div>
					</div>
				</label>
			</div>
			<div class="option regex">
				<ul>
					<li v-for="(exp,i) in label.searchExpressions" :key="exp">
						<strong :title="searchExpressions[exp]">{{ exp }}</strong>
						<NcButton type="tertiary-no-background"
							:aria-label="t('files_confidential', 'Remove search expression')"
							@click="label.searchExpressions.splice(i,1)">
							<template #icon>
								<TrashCan />
							</template>
						</NcButton>
					</li>
					<li v-for="(regex,i) in label.regularExpressions" :key="regex">
						<i>/{{ regex }}/</i>
						<NcButton type="tertiary-no-background"
							:aria-label="t('files_confidential', 'Remove search expression')"
							@click="label.regularExpressions.splice(i,1)">
							<template #icon>
								<TrashCan />
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
import TrashCan from 'vue-material-design-icons/TrashCan.vue'
import { NcSelect, NcButton } from '@nextcloud/vue'
export default {
	name: 'ClassificationLabel',
	components: {
		NcSelect,
		NcButton,
		CloseIcon,
		TrashCan,
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
.option.regex {
	position: relative;
	left: 350px;
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
