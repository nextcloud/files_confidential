<!--
  - SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->
# Confidential Files

[![REUSE status](https://api.reuse.software/badge/github.com/nextcloud/files_confidential)](https://api.reuse.software/info/github.com/nextcloud/files_confidential)

This app allows administrators to define a set of classification labels that will be assigned to files as Nextcloud tags.
For each classification label you can define a set of rules that govern when it will be assigned to a file,
based on text content or TSCP/BAILS classification metadata.

The assigned tags can then be used with the files_accesscontrol app to restrict access to specific groups of users.


## Install
* Place this app in **nextcloud/apps/**

or

* Install from the Nextcloud appstore

## Building the app

The app can be built by using the provided Makefile by running:

    make

This requires the following things to be present:
* make
* which
* tar: for building the archive
* curl: used if phpunit and composer are not installed to fetch them from the web
* npm: for building and testing everything JS, only required if a package.json is placed inside the **js/** folder
