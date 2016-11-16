<?php
/*
Plugin Name: E20R: Single Use Trial Subscription for Paid Memberships Pro
Plugin URI: https://eighty20results.com/wordpress-plugin/e20r-single-use-trial/
Description: Allow a member to sign up for the trial membership level once
Version: 1.1
Author: Thomas Sjolshagen @ Eighty/20 Results by Wicked Strong Chicks, LLC <thomas@eighty20results.com>
Author URI: http://www.eighty20results.com/thomas-sjolshagen/
Language: e20rsut
License: GPL v2
*/
/**
 * Copyright (c) 2016 - Eighty/20 Results (Thomas Sjolshagen <thomas@eighty20results.com>). ALL RIGHTS RESERVED
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

	$level_id = isset( $_REQUEST['edit'] ) ? intval( $_REQUEST['edit'] ) : null;

	$level          = pmpro_getLevel( $level_id );
	$level_settings = get_option( 'e20rsut_settings', false );

	if (WP_DEBUG) {
		error_log("The current level's settings: " . print_r( $level, true));
	}

	if ( empty( $level_id ) || ( ! empty( $level_id ) && pmpro_isLevelFree( $level ) ) ||
	     ( empty( $level->initial_payment ) && ( ! empty( $level->billing_amount ) && ! empty( $level->cycle_number ) ) ) ||
	     ( $level->trial_amouunt != 0 )
	) {
		?>
		<h3 class="topborder"><?php _e('Single Use Trial Settings', 'e20rsut');?></h3>
		<p class="e20r-description"><?php _e("Should we prevent members from signing up for this free/trial membership level more than once?", "e20rsut"); ?></p>
		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row" valign="top"><label
						for="e20r-single-use-trial"><?php _e( "Limit trial to single use?", "e20rsut" ) ?></label></th>
				<td>
					<input type="checkbox" name="e20r-single-use-trial" id="e20r-single-use-trial"
					       value="1" <?php isset( $level_settings[ $level_id ] ) ? checked( boolval( $level_settings[ $level_id ] ), true ) : null; ?>>
				</td>
			</tr>
			</tbody>
		</table>
		<?php
	}
}

add_action( 'pmpro_membership_level_after_other_settings', 'e20r_single_use_trial_settings' );

/**
 * Save settings for a given membership level.
 *
 * @param int $level_id ID of level being saved
 */
function e20r_save_single_use_trial( $level_id ) {

	$options = get_option( 'e20rsut_settings', false );
	$setting = isset( $_REQUEST['e20r-single-use-trial'] ) ? boolval( $_REQUEST['e20r-single-use-trial'] ) : false;

	if ( ! is_array( $options ) ) {
		$options = array();
	}

	$options[ $level_id ] = $setting;

	update_option( 'e20rsut_settings', $options, false );
}

add_action( 'pmpro_save_membership_level', 'e20r_save_single_use_trial' );
/**
 * Filter to return all free levels as "single-use trial membership levels"
 *
 * @param   array $level_array Array of level IDs (one or more).
 *
 * @return  array                     Array of level ID(s).
 *
 */
function e20r_set_trial_levels( $level_array ) {

	if ( function_exists( 'pmpro_isLevelFree' ) &&
	     true === apply_filters( 'e20r-all-free-levels-are-single-use-trials', false ) ) {

		$all_levels = pmpro_getAllLevels( true, true );

		// Add all free levels (trials?) to the filter array
		foreach ( $all_levels as $level_id => $level ) {

			if ( pmpro_isLevelFree( $level ) ) {
				$level_array[] = $level_id;
			}
		}
	 } else {

		$settings = get_option( 'e20rsut_settings', false );

		foreach( $settings as $level_id => $is_sut ) {

			if ( false != $is_sut ) {
				$level_array[] = $level_id;
			}
		}
	}

	return $level_array;
}
add_filter('e20r_set_single_use_trial_level_ids', 'e20r_set_trial_levels', 1, 1);

//record when users gain the trial level
function e20r_after_change_membership_level( $level_id, $user_id ) {
	//trial level(s) to allow single sign-ups
	$trial_levels = apply_filters( 'e20r_set_single_use_trial_level_ids', array() );

	if ( in_array( $level_id, $trial_levels ) ) {
		//add user meta to record the fact that this user has had this level before
		update_user_meta( $user_id, "e20r_trial_level_{$level_id}_used", true );
	}
}

add_action( "pmpro_after_change_membership_level", "e20r_after_change_membership_level", 10, 2 );

//check at checkout if the user has used the trial level already
function e20r_registration_checks( $value ) {
	global $current_user;

	// array of trial levels
	$trial_levels = apply_filters( 'e20r_set_single_use_trial_level_ids', array() );
	$level_id     = intval( $_REQUEST['level'] );

	if ( $current_user->ID && in_array( $level_id, $trial_levels ) ) {
		//check if the current user has already used the trial level
		$already = get_user_meta( $current_user->ID, "e20r_trial_level_{$level_id}_used", true );

		//yup, don't let them checkout
		if ( ! empty( $already ) ) {

			global $pmpro_msg, $pmpro_msgt;
			$pmpro_msg  = __( "You have already used your trial subscription. Please select a full subscription to checkout.", "e20rsut" );
			$pmpro_msgt = "pmpro_error";

			$value = false;
		}
	}

	return $value;
}

add_filter( "pmpro_registration_checks", "e20r_registration_checks" );

//swap the expiration text if the user has used the trial
function e20r_level_expiration_text( $text, $level ) {
	global $current_user;

	$level_id     = $level->id;
	$trial_levels = apply_filters( 'e20r_set_trial_level_ids', array() );
	$has_used     = get_user_meta( $current_user->ID, "e20r_trial_level_{$level_id}_used", true );

	if ( ! empty( $current_user->ID ) && ! empty( $has_used ) && in_array( $level_id, $trial_levels ) ) {
		$text = __( "You have already used your trial subscription. Please select a full subscription to checkout.", "e20rsut" );
	}

	return $text;
}

add_filter( "pmpro_level_expiration_text", "e20r_level_expiration_text", 10, 2 );

if (!function_exists('e20r_force_tls_12')) {
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

add_action( 'http_api_curl', 'e20r_force_tls_12' );

if ( !function_exists( 'boolval' ) ) {
	/**
	 * Get the boolean value of a variable
	 *
	 * @param mixed $var    Teh scalar value being converted to a boolean
	 *
	 * @return bool The boolean value of $var
	 */
	function boolval( $var ) {
		return !! $var;
	}
}

// One-click update handler
if ( ! class_exists( '\\PucFactory' ) ) {
	require_once( plugin_dir_path( __FILE__ ) . 'plugin-updates/plugin-update-checker.php' );
}

$plugin_updates = \PucFactory::buildUpdateChecker(
	'https://eighty20results.com/protected-content/e20r-single-use-trial/metadata.json',
	__FILE__,
	'e20r-single-use-trial'
);
