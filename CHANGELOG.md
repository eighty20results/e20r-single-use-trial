# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## v2.3 - 2022-02-07
- BUG FIX: Strip away unused acceptance tests (Eighty/20Results Bot on Github)
- BUG FIX: Fixed some of the documentation (Eighty/20Results Bot on Github)
- BUG FIX: Updated the CI/CD pipeline config (Eighty/20Results Bot on Github)
- BUG FIX: Refactored the plugin sources (Eighty/20Results Bot on Github)
- BUG FIX: Refactored Unit Tests (Eighty/20Results Bot on Github)
- BUG FIX: Incorporate the Unit/Integration/++ docker environment for CI/CD (Eighty/20Results Bot on Github)
- BUG FIX: Missing env settings used by the docker build script (Eighty/20Results Bot on Github)
- BUG FIX: Adding docs for the FILTERS and ACTIONS used by the plugin (Eighty/20Results Bot on Github)
- BUG FIX: Refactored and using PSR-4 autoloader (Eighty/20Results Bot on Github)
- BUG FIX: Updated CI/CD pipeline helpers and config (Eighty/20Results Bot on Github)
- BUG FIX: Make sure the build script works in a GitHub action (Thomas Sjolshagen)
- BUG FIX: Fatal error during install of production composer dependencies (Thomas Sjolshagen)
- BUG FIX: Reverting removal of class-settings.php (Thomas Sjolshagen)
- BUG FIX: Fatal errors during upgrade to new modular E20R Utilities module (Thomas Sjolshagen)
- BUG FIX: Update build scripts (Thomas Sjolshagen)
- BUG FIX: .gitignore cleanup (Thomas Sjolshagen)
- BUG FIX: Various updates to fix the Unit tests (Thomas Sjolshagen)
- BUG FIX: Renamed source file for the Utilities module checker (Thomas Sjolshagen)
- BUG FIX: Use stripped down .SQL file for database when testing and add the Dockerfile for WP stack based testing (Thomas Sjolshagen)
- BUG FIX: Don't fight the IDE's syntax checking (Thomas Sjolshagen)
- BUG FIX: Stop using CircleCI for testing (Thomas Sjolshagen)
- BUG FIX: Moved assets to .wordpress-org/ directory (Thomas Sjolshagen)
- BUG FIX: Initial commit of new Makefile and private config for Makefile (Thomas Sjolshagen)
- BUG FIX: Use makefile to build change log and documentation (Thomas Sjolshagen)
- BUG FIX: Refactored for use with codeception and renamed Unit Test files (Thomas Sjolshagen)
- BUG FIX: More clean-up (Thomas Sjolshagen)
- BUG FIX: GitHub workflow definitions (Thomas Sjolshagen)
- BUG FIX: Standard scripts for Makefile (Thomas Sjolshagen)
- BUG FIX: Clean-up as part of transition to codeception/Makefile/GitHub Action based CI/CD pipeline (Thomas Sjolshagen)
- BUG FIX: Remove built-in Utilities module (Refactored) (Thomas Sjolshagen)
- BUG FIX: Configure codeception testing (Thomas Sjolshagen)
- BUG FIX: Updated composer.json (Thomas Sjolshagen)
- ENH: Adding WP/codeception dockerfile (Thomas Sjolshagen)
- ENH: Initial commit (Thomas Sjolshagen)
- Various updates to try and get the wp install going (Thomas Sjolshagen)
- ENH: Renamed multisite setup script (Thomas Sjolshagen)
- BUG FIX: Unittesting didn't work due to idiocy (Thomas Sjolshagen)
- BUG FIX: Various updates to pass Code Standard tests (Thomas Sjolshagen)
- BUG FIX: Unneeded debug stuffs (Thomas Sjolshagen)
- Renamed (Thomas Sjolshagen)
- ENH: PHPCS uses WordPress standard (Thomas Sjolshagen)
- ENH: Initial commit for generating a GitHub release (Thomas Sjolshagen)
- BUG FIX: Resolve CircleCI issues when running locally (Thomas Sjolshagen)
- BUG FIX: circleci execution not working as expected locally (Thomas Sjolshagen)
- BUG FIX: Tried to get unittesting going for < PHP v7.3 (Thomas Sjolshagen)
- Re-adding GRUMPHP (Thomas Sjolshagen)
- ENH: Updated composer dependencies (Thomas Sjolshagen)
- ENH: Adding CircleCI shield for builds (Thomas Sjolshagen)
- BUG FIX: Using <<< w/yaml is a pain! (Thomas Sjolshagen)
- BUG FIX: <<< Needs to be escaped for CircleCI (Thomas Sjolshagen)
- ENH: Adding more PHP version unit tests (Thomas Sjolshagen)
- ENH: Adding WP Code Standards (Thomas Sjolshagen)
- REFACTOR: Moved database and unused configuration files for testing (Thomas Sjolshagen)
- ENH: Initial commit for Codeception config (Thomas Sjolshagen)
- BUG FIX: Bad merge causing unit test to fail (Thomas Sjolshagen)
- MISC: Updated composer (Thomas Sjolshagen)
- Added Unit Tests (#6) (Thomas Sjolshagen)
- Initial commit of LICENSE.txt file (Thomas Sjolshagen)
- NIT: Comments & whitespace (Thomas Sjolshagen)
- Update change logs and version number (v2.2) (Thomas Sjolshagen)

## 2.3 

* REFACTOR: Remove submodule and add Utilities as subtree instead
* REFACTOR: Namespace clarity
* ENH: Adding CircleCI assets
* ENH: Adding a couple of unittests
* ENH: Updated .gitattributes and .gitignore
* ENH: Initial commit for circleci configuration file
* ENH: Added all of the included composer files
* ENH: Updated grumphp.yml config
* BUG FIX: composer 'vendor' directory is called 'inc' in this repo
* BUG FIX: Less complex code when updating trial levels
* BUG FIX: Shouldn't allow levels to be added if they're not free (when the 'all free levels are trial levels' filter is true
* BUG FIX: Removed unused variable
* BUG FIX: Added required tools and less stuff in inc/
* BUG FIX: Added path to commands and exclude inc
* BUG FIX: Added xdebug-handler code for composer
* BUG FIX: More linting tools for CircleCI
* BUG FIX: Not using vendor
* BUG FIX: Not using mock-function
* BUG FIX: Exclude all composer dependencies
* BUG FIX: Adding namespace so updated filter/action hooks
* BUG FIX: Only supporting PHP 7.2 -> 7.4
* BUG FIX: Using phpunit 8.5.x

## 2.2 

* ENH: Added link to GitHub Issues page for the plugin
* ENH: Incorrect slug name for the plugin in docker environment
* ENH: Added PMPro PayFast gateway plugin testing to test suite
* BUG FIX: Fatal error due to undefined class/function

## 2.1 

* ENH: Added docker environment for test purposes
* ENH: Using autoloader to load Utilities class

## 2.0 

* ENHANCEMENT: Use the E20R Utilities module for sanitizing REQUEST variables
* ENHANCEMENT: Use the E20R Utilities module to handle plugin update logic
* ENHANCEMENT: Updated copyright notice

## 1.2 

* BUG/ENHANCEMENT: Expanded to allow single-use configuration for any membership type, not just free levels.

## 1.1 

* ENH/BUG: Added custom boolval() for ancient versions of PHP

## 1.0.4 

* ENH: Add GPL v2 license text to source file.
* ENH: Add descriptive text to settings section on Membership Level definition page

## 1.0.3 

* BUG: Didn't always load settings on page

## 1.0.2 

* BUG/ENH: Make e20r_force_tls_12() pluggable
* ENH: Add header text to settings page

## 1.0.1 

* Adding debug capabilities

## 1.0 

* Initial Release of the add-on
