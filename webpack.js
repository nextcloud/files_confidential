/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

const path = require('path')
const webpackConfig = require('@nextcloud/webpack-vue-config')

const buildMode = process.env.NODE_ENV
const isDev = buildMode === 'development'

// Generate reuse license files if not in development mode
if (!isDev) {
	const WebpackSPDXPlugin = require('./build-js/WebpackSPDXPlugin.js')
	webpackConfig.plugins.push(new WebpackSPDXPlugin({
		override: {
			select2: 'MIT',
		},
	}))

	webpackConfig.optimization.minimizer = [{
		apply: (compiler) => {
			// Lazy load the Terser plugin
			const TerserPlugin = require('terser-webpack-plugin')
			new TerserPlugin({
				extractComments: false,
				terserOptions: {
					format: {
						comments: false,
					},
					compress: {
						passes: 2,
					},
				},
		  }).apply(compiler)
		},
	}]
}

module.exports = webpackConfig
webpackConfig.entry.admin = path.join(__dirname, 'src', 'admin.js')
