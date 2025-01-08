<!--
  - SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->
# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [3.0.3] - 2024-08-07

### Fixed

* Do not crash entire request with file parsing error. #149
* ClassificationLabel: convert display name to string when filtering. #147 @DorraJaouad

## [3.0.2] - 2024-07-02

### Fixed

* Do not add keywords for imported labels

## [3.0.1] - 2024-06-19

### Fixed

* Don't discard rules when upgrading from 2.x to 3.x

## [3.0.0] - 2024-06-18

### Breaking changes

* Now requires php >= 8.0

### Fixed

* fix(MicrosoftContentProvider): Allow reading multiple headers and footers
* feat: Add PDF content provider
* feat: Add PlainTextContentProvider
* fix: Add "or" to make the logical conjunction clear
* feat: Allow classifications based on metadata properties
* fix img for theming (dark/light mode)

## [2.1.0] - 2024-03-06

Maintenance update.

### Added

- Added support for Nextcloud 29

### Fixed

- Fixed upload of policy does not reload schemas in admin settings
- Fixes other minor visual issues on the admin settings page

### Updated

- Updated npm packages (security, Nextcloud Vue 8)

## [2.0.1] - 2023-11-08

### Fixed

- Update dependencies

## [2.0.0] - 2023-10-31

### Breaking changes

- Drop support for Nextcloud 25

### Changes

- Add support for Nextcloud 28

### Fixed

- Fix(l10n): Update translations from Transifex

## [1.0.5] - 2023-09-06

### Fixed

- Check in lockfile

## [1.0.4] - 2023-08-28

### Fixed

- Fix classloaders

## [1.0.3] - 2023-06-30

### Fixed
- Fix type error

## [1.0.2] - 2023-04-28

### Fixed
 - Updated dependencies
 - Remove default labels
 - Update translations

## [1.0.0] - 2023-03-15
Initial version
