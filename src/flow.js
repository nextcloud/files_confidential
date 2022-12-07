import ClassificationLabelCheck from './components/ClassificationLabelCheck.vue'

window.OCA.WorkflowEngine.registerCheck({
	class: 'OCA\\Files_Confidential\\WorkflowEngine\\Check\\ClassificationLabelCheck',
	name: t('files_confidential', 'Classification label'),
	operators: [
		{ operator: 'less', name: t('workflowengine', 'is less') },
		{ operator: '!less', name: t('workflowengine', 'is higher/equal') },
		{ operator: 'greater', name: t('workflowengine', 'is higher') },
		{ operator: '!greater', name: t('workflowengine', 'is lower/equal') },
		{ operator: 'equal', name: t('workflowengine', 'is equal') },
	],
	component: ClassificationLabelCheck,
},)
