<?php
/*
 * Copyright (c) 2016 - 2021. - Eighty / 20 Results by Wicked Strong Chicks <thomas@eighty20results.com>. ALL RIGHTS RESERVED
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

namespace E20R\SingleUseTrial;

/*
Plugin Name: E20R: Single Use Trial Subscription for Paid Memberships Pro
Plugin URI: https://eighty20results.com/wordpress-plugin/e20r-single-use-trial/
Description: Allow a member to sign up for the trial membership level once
Version: 2.3
Author: Thomas Sjolshagen @ Eighty/20 Results by Wicked Strong Chicks, LLC <thomas@eighty20results.com>
Author URI: http://www.eighty20results.com/thomas-sjolshagen/
Domain: e20r-single-use-trial
License: GPLv2

 * Copyright (c) 2016-2020 - Eighty/20 Results (Thomas Sjolshagen <thomas@eighty20results.com>). ALL RIGHTS RESERVED
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
 *
 **/

use E20R\SingleUseTrial\Views\Settings;
use E20R\Utilities\Utilities;

/**
 * Configuration section on the Membership Levels page (in settings).
 *
 * @return string|null
 *
 * @uses $_REQUEST['edit']
 */
function e20r_single_use_trial_settings() {

	$utils          = Utilities::get_instance();
	$level_id       = $utils->get_variable( 'edit', null );
	$level_settings = \get_option( 'e20rsut_settings', false );
	$html           = Settings::membership_level( $level_settings, $level_id );

	if ( isset( $_SERVER['REQUEST_METHOD'] ) ) {
		echo $html; // phpcs:ignore
		return null;
	}

	return $html;
}

\add_action(
	'pmpro_membership_level_after_other_settings',
	'E20R\SingleUseTrial\e20r_single_use_trial_settings'
);

/**
 * Save settings for a given membership level.
 *
 * @param int $level_id ID of level being saved
 *
 * @return bool
 */
function e20r_save_single_use_trial( $level_id = 0 ) {

	$utils   = Utilities::get_instance();
	$options = get_option( 'e20rsut_settings', false );

	// Make sure we have a valid Level ID number to process
	if ( 0 === $level_id ) {
		return false;
	}

	// Get the setting value from the $_REQUEST array
	$setting = (bool) $utils->get_variable( 'e20r-single-use-trial', false );

	// If the options are empty or not an array, create one (array)
	if ( false === $options || ! is_array( $options ) ) {
		$options = array();
	}

	// Set the single-use trial setting for the specific level ID
	$options[ $level_id ] = $setting;

	// Save the settings
	return update_option( 'e20rsut_settings', $options, 'no' );
}

add_action( 'pmpro_save_membership_level', 'E20R\SingleUseTrial\e20r_save_single_use_trial' );

/**
 * Grab levels that are counted as "single-use trial membership levels"
 *
 * @param array $level_array Array of level IDs (one or more).
 *
 * @return int[]             Array of level ID(s).
 *
 */

function e20r_get_trial_levels( $level_array ) {

	if ( function_exists( 'pmpro_isLevelFree' ) &&
		true === apply_filters( 'e20r_all_free_levels_are_single_use_trials', false ) ) {

		$all_levels = pmpro_getAllLevels( true, true );

		// Add all free levels (trials?) to the filter array
		foreach ( $all_levels as $level ) {
			$level_array = e20r_update_trial_levels( $level_array, $level );
		}
	} else {

		$settings = get_option( 'e20rsut_settings', false );
		foreach ( $settings as $level_id => $is_sut ) {
			if ( false !== $is_sut && ! in_array( $level_id, $level_array, true ) ) {
				$level_array[] = $level_id;
			}
		}
	}

	return $level_array;
}

add_filter(
	'e20r_set_single_use_trial_level_ids',
	'E20R\SingleUseTrial\e20r_get_trial_levels',
	1,
	1
);

/**
 * Update the list of membership level IDs that should be trial level(s)
 *
 * @param array $level_array
 * @param \stdClass $level
 *
 * @return int[]
 */
function e20r_update_trial_levels( $level_array, $level ) {

	// If the level is free _and_ not in the list already
	if ( true === pmpro_isLevelFree( $level ) &&
		! in_array( $level->id, $level_array, true )
	) {
		$level_array[] = $level->id;
		// Don't allow the inclusion of levels that aren't free
		// And has to exist in the list obviously...
	} elseif ( false === pmpro_isLevelFree( $level ) &&
		false !== ( $l_key = array_search( $level->id, $level_array, true ) ) ) { // phpcs:ignore
		unset( $level_array[ $l_key ] );
	}

	return $level_array;
}

/**
 * Record that a user checked out for a trial membership
 *
 * @param int $level_id
 * @param int $user_id
 */
function e20r_after_change_membership_level( $level_id, $user_id ) {

	// Level(s) where we limit sign-up to one for a given user ID
	$trial_levels = apply_filters( 'e20r_set_single_use_trial_level_ids', array() );

	if ( in_array( $level_id, $trial_levels, true ) ) {
		// Usermeta is used to record the user's previous trial (free) membership level(s)
		update_user_meta( $user_id, "e20r_trial_level_{$level_id}_used", true );
	}
}

add_action( 'pmpro_after_change_membership_level', 'E20R\SingleUseTrial\e20r_after_change_membership_level', 10, 2 );

/**
 * During checkout (for renewals), verify if the user has consumed their trial already.
 *
 * @param bool $value
 *
 * @return bool
 */
function e20r_registration_checks( $value ) {

	// Skip if we're not logged in - so not a renewal
	if ( function_exists( 'is_user_logged_in' ) && false === \is_user_logged_in() ) {
		return $value;
	}

	/** List of levels with trial policy set */
	$trial_levels = \apply_filters( 'e20r_set_single_use_trial_level_ids', array() );
	$utils        = Utilities::get_instance();
	$level_id     = $utils->get_variable( 'level', null );
	$user         = \wp_get_current_user();

	if ( $user->ID && in_array( $level_id, $trial_levels, true ) ) {

		// Does the currently logged in user have a trial level they've used
		$already = \get_user_meta( $user->ID, "e20r_trial_level_{$level_id}_used", true );

		if ( ! empty( $already ) ) {

			global $pmpro_msg, $pmpro_msgt;

			$pmpro_msg  = \__(
				'You have already used your trial subscription. Please select a full subscription to checkout.',
				'e20r-single-use-trial'
			);
			$pmpro_msgt = 'pmpro_error';

			$value = false;
		}
	}

	return $value;
}

\add_filter( 'pmpro_registration_checks', 'E20R\SingleUseTrial\e20r_registration_checks' );

/**
 * Change the error message text when selecting membership level after the trial has been used
 *
 * @param string $text - The text to show when there is an error
 * @param \stdClass $level - The membership Level info
 *
 * @return string|void
 *
 * @filter e20r_set_trial_level_ids
 */
function e20r_level_expiration_text( $text, $level ) {

	$user_id      = get_current_user_id();
	$level_id     = $level->id;
	$trial_levels = apply_filters( 'e20r_set_trial_level_ids', array() );
	$has_used     = get_user_meta( $user_id, "e20r_trial_level_{$level_id}_used", true );

	if ( ! empty( $user_id ) && ! empty( $has_used ) && in_array( $level_id, $trial_levels, true ) ) {

		$text = __(
			'You have already used your trial subscription. Please select a full subscription to checkout.',
			'e20r-single-use-trial'
		);
	}

	return $text;
}

add_filter( 'pmpro_level_expiration_text', 'E20R\SingleUseTrial\e20r_level_expiration_text', 10, 2 );

if ( ! function_exists( 'e20r_force_tls_12' ) ) {
	/**
	 * Connect to the license server using TLS 1.2
	 *
	 * @param $handle - File handle for the pipe to the CURL process
	 */
	function e20r_force_tls_12( $handle ) {

		// set the CURL option to use.
		curl_setopt( $handle, CURLOPT_SSLVERSION, 6 ); // phpcs:ignore
	}
}

add_action( 'http_api_curl', 'E20R\SingleUseTrial\e20r_force_tls_12' );

if ( ! function_exists( 'boolval' ) ) {
	/**
	 * Get the boolean value of a variable
	 *
	 * @param mixed $var The scalar value being converted to a boolean
	 *
	 * @return bool The boolean value of $var
	 */
	function boolval( $var ) {
		return ! ! $var;
	}
}

if ( ! function_exists( 'E20R\SingleUseTrial\e20r_auto_loader' ) ) {
	/**
	 * Auto-loader for the E20R Single Use Trial plugin
	 *
	 * @param string $class_name Name of the class to auto-load
	 *
	 * @since  1.0
	 * @access public static
	 */
	function e20r_auto_loader( $class_name ) {

		if ( false === stripos( $class_name, 'e20r' ) ) {
			return;
		}

		$parts      = explode( '\\', $class_name );
		$c_name     = strtolower( preg_replace( '/_/', '-', $parts[ ( count( $parts ) - 1 ) ] ) );
		$base_paths = array();

		if ( file_exists( plugin_dir_path( __FILE__ ) . 'src/' ) ) {
			$base_paths[] = plugin_dir_path( __FILE__ ) . 'src/';
		}

		if ( file_exists( plugin_dir_path( __FILE__ ) . 'classes/' ) ) {
			$base_paths[] = plugin_dir_path( __FILE__ ) . 'classes/';
		}

		if ( file_exists( plugin_dir_path( __FILE__ ) . 'class/' ) ) {
			$base_paths[] = plugin_dir_path( __FILE__ ) . 'class/';
		}

		if ( file_exists( plugin_dir_path( __FILE__ ) . 'blocks/' ) ) {
			$base_paths[] = plugin_dir_path( __FILE__ ) . 'blocks/';
		}

		$filename = "class-{$c_name}.php";

		foreach ( $base_paths as $base_path ) {

			try {
				$iterator = new \RecursiveDirectoryIterator(
					$base_path,
					\RecursiveDirectoryIterator::SKIP_DOTS |
					\RecursiveIteratorIterator::SELF_FIRST |
					\RecursiveIteratorIterator::CATCH_GET_CHILD |
					\RecursiveDirectoryIterator::FOLLOW_SYMLINKS
				);
			} catch ( \Exception $e ) {
				print 'Error: ' . $e->getMessage(); // phpcs:ignore
				return;
			}

			try {
				$filter = new \RecursiveCallbackFilterIterator(
					$iterator,
					function ( $current, $key, $iterator ) use ( $filename ) {

						// Skip hidden files and directories.
						if ( '.' === $current->getFilename()[0] || '..' === $current->getFilename() ) {
							return false;
						}

						if ( $current->isDir() ) {
							// Only recurse into intended subdirectories.
							return $current->getFilename() === $filename;
						} else {
							// Only consume files of interest.
							return str_starts_with( $current->getFilename(), $filename );
						}
					}
				);
			} catch ( \Exception $e ) {
				echo 'Autoloader error: ' . $e->getMessage(); // phpcs:ignore
				return;
			}

			foreach ( new \RecursiveIteratorIterator( $iterator ) as $f_filename => $f_file ) {

				$class_path = $f_file->getPath() . '/' . $f_file->getFilename();

				if ( $f_file->isFile() && false !== stripos( $class_path, $filename ) ) {

					require_once $class_path;
				}
			}
		}
	}
}

/**
 * Load the required E20R Utilities Module functionality
 */
require_once plugin_dir_path( __FILE__ ) . "class-activateutilitiesplugin.php";

if ( false === apply_filters( 'e20r_utilities_module_installed', false ) ) {

	$required_plugin = "E20R: Single Use Trial Subscription for Paid Memberships Pro";

	if ( false === \E20R\Utilities\ActivateUtilitiesPlugin::attempt_activation() ) {
		add_action( 'admin_notices', function () use ( $required_plugin ) {
			\E20R\Utilities\ActivateUtilitiesPlugin::plugin_not_installed( $required_plugin );
		} );

		return false;
	}
}

// The one--click update handler
try {
	spl_autoload_register( 'E20R\SingleUseTrial\e20r_auto_loader' );
} catch ( \Exception $exception ) {
	// phpcs:ignore
	error_log( 'Unable to register autoloader: ' . $exception->getMessage(), E_USER_ERROR );
	return false;
}

if ( class_exists( 'E20R\Utilities\Utilities' ) ) {
	Utilities::configure_update(
		'e20r-single-use-trial',
		plugin_dir_path(__FILE__) . 'e20r-single-use-trial.php'
	);
}
