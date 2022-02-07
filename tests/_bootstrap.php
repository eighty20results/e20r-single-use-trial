<?php
/*
 * Copyright (c) 2016 - 2022. - Eighty / 20 Results by Wicked Strong Chicks <thomas@eighty20results.com>. ALL RIGHTS RESERVED
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

use Codeception\Util\Autoload;

Autoload::addNamespace( 'E20R\\Tests\\Unit\\Fixtures', __DIR__ . '/unit/inc/' );
Autoload::addNamespace( 'E20R\\Utilities', __DIR__ . '/../inc/wp_plugins/00-e20r-utilities/src/E20R/Utilities' );
Autoload::addNamespace( 'E20R\\SingleUseTrial', __DIR__ . '/../src/E20R/SingleUseTrial' );
/**
 * The following snippets uses `PLUGIN` to prefix
 * the constants and class names. You should replace
 * it with something that matches your plugin name.
 */
// define test environment
define( 'PLUGIN_PHPUNIT', true );

if ( ! defined( 'PLUGIN_PATH' ) ) {
	define( 'PLUGIN_PATH', __DIR__ . '/../src/' );
}

require_once __DIR__ . '/../inc/autoload.php';

// Load the class autoloader
require_once __DIR__ . '/../e20r-single-use-trial.php';
