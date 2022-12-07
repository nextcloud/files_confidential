<!--
  - @copyright Copyright (c) 2019 Julius Härtl <jus@bitgrid.net>
  - @copyright Copyright (c) 2022 Marcel Klehr <mklehr@gmx.net>
  -
  - @author Julius Härtl <jus@bitgrid.net>
  - @author Marcel Klehr <mklehr@gmx.net>
  -
  - @license GNU AGPL version 3 or any later version
  -
  - This program is free software: you can redistribute it and/or modify
  - it under the terms of the GNU Affero General Public License as
  - published by the Free Software Foundation, either version 3 of the
  - License, or (at your option) any later version.
  -
  - This program is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  - GNU Affero General Public License for more details.
  -
  - You should have received a copy of the GNU Affero General Public License
  - along with this program. If not, see <http://www.gnu.org/licenses/>.
  -
  -->

<template>
	<div>
		<NcMultiselect :value="currentValue"
			:placeholder="t('files_confidential', 'Select a classification label')"
			:options="options"
			label="name"
			:multiple="false"
			:tagging="false"
			@input="setValue" />
	</div>
</template>

<script>
import { NcMultiselect } from '@nextcloud/vue'
import valueMixin from '../mixins/valueMixin.js'
import { loadState } from '@nextcloud/initial-state'

export default {
	name: 'ClassificationLabelCheck',
	components: {
		NcMultiselect,
	},
	mixins: [
		valueMixin,
	],
	data() {
		console.log(loadState('files_confidential', 'labels'))
		return {
			newValue: '',
			options: loadState('files_confidential', 'labels'),
		}
	},
	computed: {
		currentValue() {
			const matching = this.options.find((option) => this.newValue === option.index)
			if (matching) {
				return matching
			}
			return this.options[0]
		},
	},
	methods: {
		setValue(value) {
			if (value !== null) {
				this.newValue = value.index
				this.$emit('input', this.newValue)
			}
		},
	},
}
</script>
<style scoped lang="scss">
	.multiselect, input[type='text'] {
		width: 100%;
	}
</style>
