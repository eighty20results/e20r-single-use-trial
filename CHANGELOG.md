# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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
