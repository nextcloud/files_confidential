import Vue from 'vue'
import { translate, translatePlural } from '@nextcloud/l10n'
import Tooltip from '@nextcloud/vue/dist/Directives/Tooltip.js'
import App from './views/Admin.vue'
import AppGlobal from './mixins/AppGlobal.js'
import '@nextcloud/dialogs/style.css'

Vue.mixin(AppGlobal)
Vue.directive('tooltip', Tooltip)

Vue.prototype.t = translate
Vue.prototype.n = translatePlural
Vue.prototype.OC = window.OC
Vue.prototype.OCA = window.OCA

global.FilesConfidential = new Vue({
	el: '#files_confidential',
	render: h => h(App),
})
