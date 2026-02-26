/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import parseUrl from 'url-parse'
import { generateRemoteUrl } from '@nextcloud/router'
import { getClient } from '@nextcloud/files/dav'

export const rootPath = 'dav'

// init webdav client on default dav endpoint
const remote = generateRemoteUrl(rootPath)
const client = getClient(remote)

export const remotePath = parseUrl(remote).pathname
export default client
