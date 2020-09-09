<?php
namespace E20R\SingleUseTrial;

use E20R\SingleUseTrial\Views\Settings;
use E20R\Utilities\Utilities;
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

/**
 * Configuration section on the Membership Levels page (in settings).
 */
function e20r_single_use_trial_settings() {
	
	if ( ! function_exists( 'pmpro_getLevel' ) ) {
		return;
	}
	
	
	$utils    = Utilities::get_instance();
	$level_id = $utils->get_variable( 'edit', null );
	
	$level_settings = \get_option( 'e20rsut_settings', false );
    echo Settings::membership_level($level_settings, $level_id);
}

\add_action(
        'pmpro_membership_level_after_other_settings',
        'E20R\SingleUseTrial\e20r_single_use_trial_settings'
);

/**
 * Save settings for a given membership level.
 *
 * @param int $level_id ID of level being saved
 */
function e20r_save_single_use_trial( $level_id ) {
	
	$utils   = Utilities::get_instance();
	$options = get_option( 'e20rsut_settings', false );
	
	$setting = (bool) $utils->get_variable( 'e20r-single-use-trial', false );
	
	if ( ! is_array( $options ) ) {
		$options = array();
	}
	
	$options[ $level_id ] = $setting;
	
	update_option( 'e20rsut_settings', $options, false );
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
	     true === apply_filters( 'e20r-all-free-levels-are-single-use-trials', false ) ) {
		
		$all_levels = pmpro_getAllLevels( true, true );
		
		// Add all free levels (trials?) to the filter array
		foreach ( $all_levels as $level ) {
		    $level_array = e20r_update_trial_levels($level_array, $level );
		}
	} else {
		
		$settings = get_option( 'e20rsut_settings', false );
		foreach ( $settings as $level_id => $is_sut ) {
			if ( false != $is_sut && ! in_array( $level_id, $level_array ) ) {
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
    if ( TRUE === pmpro_isLevelFree( $level ) &&
	     ! in_array( $level->id, $level_array )
	) {
		$level_array[] = $level->id;
    // Don't allow the inclusion of levels that aren't free
	} else if ( FALSE === pmpro_isLevelFree( $level ) &&
                // And has to exist in the list obviously...
	            FALSE !== ($l_key = array_search( $level->id, $level_array ) ) ) {
		unset( $level_array[$l_key] );
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

	if ( in_array( $level_id, $trial_levels ) ) {
		// Usermeta is used to record the user's previous trial (free) membership level(s)
		update_user_meta( $user_id, "e20r_trial_level_{$level_id}_used", true );
	}
}

add_action(
        "pmpro_after_change_membership_level",
        "E20R\SingleUseTrial\e20r_after_change_membership_level",
        10,
        2
);

/**
 * During checkout (for renewals), verify if the user has consumed their trial already.
 *
 * @param bool $value
 *
 * @return bool
 */
function e20r_registration_checks( $value ) {

    // Skip if we're not logged in - so not a renewal
    if ( function_exists( 'is_user_logged_in' ) && \is_user_logged_in() == false ) {
        return $value;
    }

    /** List of levels with trial policy set */
    $trial_levels = \apply_filters( 'e20r_set_single_use_trial_level_ids', array() );

    $utils      = \E20R\Utilities\Utilities::get_instance();
    $level_id   = $utils->get_variable( 'level', null );
    $user       = \wp_get_current_user();

	if ( $user->ID && in_array( $level_id, $trial_levels ) ) {

        // Does the currently logged in user have a trial level they've used
        $already = \get_user_meta( $user->ID, "e20r_trial_level_{$level_id}_used", true );

        if ( ! empty( $already ) ) {

            global $pmpro_msg, $pmpro_msgt;

            $pmpro_msg  = \__(
                "You have already used your trial subscription. Please select a full subscription to checkout.",
                "e20r-single-use-trial"
            );
            $pmpro_msgt = "pmpro_error";

            $value = false;
        }
    }

  return $value;
}

\add_filter( "pmpro_registration_checks", 'E20R\SingleUseTrial\e20r_registration_checks' );

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

  $user_id = get_current_user_id();
  $level_id     = $level->id;
  $trial_levels = apply_filters( 'e20r_set_trial_level_ids', array() );
  $has_used     = get_user_meta( $user_id, "e20r_trial_level_{$level_id}_used", true );

  if ( ! empty( $user_id ) &&
       ! empty( $has_used ) &&
       in_array( $level_id, $trial_levels )
    ) {

		$text = __(
		          "You have already used your trial subscription. Please select a full subscription to checkout.",
              "e20r-single-use-trial"
   );
	}

	return $text;
}

add_filter(
        "pmpro_level_expiration_text",
        "E20R\SingleUseTrial\e20r_level_expiration_text",
        10,
        2
);

if ( ! function_exists( "e20r_force_tls_12" ) ) {
	/**
	 * Connect to the license server using TLS 1.2
	 *
	 * @param $handle - File handle for the pipe to the CURL process
	 */
	function e20r_force_tls_12( $handle ) {

		// set the CURL option to use.
		curl_setopt( $handle, CURLOPT_SSLVERSION, 6 );
	}
}

add_action( "http_api_curl", "E20R\SingleUseTrial\e20r_force_tls_12" );

if ( ! function_exists( "boolval" ) ) {
	/**
	 * Get the boolean value of a variable
	 *
	 * @param mixed $var Teh scalar value being converted to a boolean
	 *
	 * @return bool The boolean value of $var
	 */
	function boolval( $var ) {
		return !! $var;
	}
}

if ( !function_exists( "E20R\SingleUseTrial\e20r_auto_loader" ) ) {
	/**
	 * Class auto-loader for the Enhanced Members List plugin
	 *
	 * @param string $class_name Name of the class to auto-load
	 *
	 * @since  1.0
	 * @access public static
	 */
	function e20r_auto_loader( $class_name ) {

		if ( false === stripos( $class_name, 'e20r' ) ) {
			return false;
		}

		$parts     = explode( '\\', $class_name );
		$c_name    = strtolower(
                preg_replace(
                        '/_/',
                        '-',
                        $parts[ ( count( $parts ) - 1 ) ]
                    )
        );
		$base_path = plugin_dir_path( __FILE__ ) . 'classes/';

		if ( file_exists( plugin_dir_path( __FILE__ ) . 'inc/' ) ) {
			$base_path = plugin_dir_path( __FILE__ ) . 'inc/';
		}

		$filename = "class.{$c_name}.php";
		$iterator = new \RecursiveDirectoryIterator(
		        $base_path,
                \RecursiveDirectoryIterator::SKIP_DOTS |
                \RecursiveIteratorIterator::SELF_FIRST |
                \RecursiveIteratorIterator::CATCH_GET_CHILD |
                \RecursiveDirectoryIterator::FOLLOW_SYMLINKS);

        // Locate class member files, recursively
        $filter = new \RecursiveCallbackFilterIterator( $iterator,
                /**
                 * @SuppressWarnings("unused")
                 */
        function ( $current, $key, $iterator ) use ( $filename ) {

			    $file_name = $current->getFilename();

          // Skip hidden files and directories.
          if ( $file_name[0] == '.' || $file_name == '..' ) {
            return false;
          }

			    if ( $current->isDir() ) {
				    // Only recurse into intended subdirectories.
				    return $file_name() === $filename;
			    } else {
				    // Only consume files of interest.
				    return strpos( $file_name, $filename ) === 0;
			    }
		    });

    /**
		 * @SuppressWarnings ("unused")
		 */
		foreach ( new \RecursiveIteratorIterator( $iterator ) as $f_filename => $f_file ) {

			$class_path = $f_file->getPath() . "/" . $f_file->getFilename();

			if ( $f_file->isFile() && false !== strpos( $class_path, $filename ) ) {
                print("Found {$class_name}\n");
				/**
				 * @noinspection PhpIncludeInspection
				 */
				require_once( $class_path );
			}
		}
	}
}

// The one--click update handler
try {
	spl_autoload_register( 'E20R\SingleUseTrial\e20r_auto_loader' );
} catch( \Exception $exception ) {
	error_log( "Unable to register autoloader: " . $exception->getMessage(),E_USER_ERROR );
	return false;
}

Utilities::configureUpdateServerV4(
        "e20r-single-use-trial",
        plugin_dir_path( __FILE__ ) . "e20r-single-use-trial.php"
);

