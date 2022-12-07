import Vue from 'vue'
import Tooltip from '@nextcloud/vue/dist/Directives/Tooltip.js'
import App from './views/Admin.vue'
import AppGlobal from './mixins/AppGlobal.js'

Vue.mixin(AppGlobal)
Vue.directive('tooltip', Tooltip)

global.FilesConfidential = new Vue({
	el: '#files_confidential',
	render: h => h(App),
})
