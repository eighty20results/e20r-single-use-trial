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

namespace E20R\SingleUseTrial;

use E20R\SingleUseTrial\Exceptions\InvalidMembershipLevel;
use E20R\SingleUseTrial\Views\Settings_View;
use E20R\Utilities\Utilities;
use E20R\Utilities\Message;

/**
 * Manages the Settings for the plugin
 */
class Settings {

	/**
	 * Instance of the Utilities class
	 *
	 * @var Utilities|null $utils
	 */
	private $utils;

	/**
	 * Instance of the Settings_View() class
	 *
	 * @var Settings_View|null $view
	 */
	private $view;

	/**
	 * The per-level trial membership setting(s)
	 *
	 * @var array|false|null $options
	 */
	private $options = null;

	/**
	 * List of membership level IDs that are considered trial level(s)
	 *
	 * @var int[] $trial_level_array
	 */
	private $trial_level_array = array();

	/**
	 * Constructor for the E20R Single Use Trial settings class
	 *
	 * @param Settings_View|null $view The Settings_View class
	 * @param Utilities|null     $utils The Utilities class
	 * @param array              $options Default settings to use
	 */
	public function __construct( $view = null, $utils = null, $options = null ) {

		if ( empty( $utils ) ) {
			$message = new Message();
			$utils   = new Utilities( $message );
		}
		$this->utils = $utils;

		if ( empty( $view ) ) {
			$view = new Settings_View();
		}

		$this->view = $view;

		if ( empty( $options ) ) {
			$options = get_option( 'e20rsut_settings', false );
		}

		$this->options = $options;
	}

	/**
	 * Returns the Settings HTML for the Level Settings page
	 *
	 * @return string|null
	 *
	 * @throws InvalidMembershipLevel Raised if there is no membership level included in the $_REQUEST[] array
	 */
	public function load_view() {
		$level_id = $this->utils->get_variable( 'edit', null );

		if ( null === $this ) {
			throw new InvalidMembershipLevel(
				esc_attr__( 'No membership level was supplied', 'e20r-single-use-trial' )
			);
		}

		$html = $this->view->level( $this->options, $level_id );

		if ( isset( $_SERVER['REQUEST_METHOD'] ) ) {
			echo esc_html( $html );
			return null;
		}

		return $html;
	}

	/**
	 * Load settings for the E20R Single Use Trial Membership settings plugin
	 *
	 * @return void
	 */
	public function load() {

	}
	/**
	 * Save the Single Use Trial settings for PMPro Membership Levels
	 *
	 * @param int $level_id The membership level ID to save settings for
	 *
	 * @return bool
	 */
	public function save( $level_id = 0 ) {

		// Make sure we have a valid Level ID number to process
		if ( 0 === $level_id ) {
			return false;
		}

		// Get the setting value from the $_REQUEST array
		$setting = (bool) $this->utils->get_variable( 'e20r-single-use-trial', false );

		// If the options are empty or not an array, create one (array)
		if ( false === $this->options || ! is_array( $this->options ) ) {
			$this->options = array();
		}

		// Set the single-use trial setting for the specific level ID
		$this->options[ $level_id ] = $setting;

		// Save the options
		return update_option( 'e20rsut_settings', $this->options, 'no' );
	}

	/**
	 * Set/Update the list of membership level IDs that should be considered trial level(s)
	 *
	 * @param array     $level_array The list of PMPro trial level(s)
	 * @param \stdClass $level The membership level definition
	 *
	 * @return int[]
	 */
	public function set_trial_levels( $level_array, $level ) {

		if ( ! empty( $level_array ) ) {
			$this->trial_level_array = $level_array;
		}

		$l_key = array_search( $level->id, $level_array, true );

		// If the level is free _and_ not in the list already
		if (
			true === pmpro_isLevelFree( $level ) &&
			! in_array( $level->id, $this->trial_level_array, true )
		) {
			$level_array[] = $level->id;
			// Don't allow the inclusion of levels that aren't free
			// And has to exist in the list, obviously...
		} elseif ( false === pmpro_isLevelFree( $level ) && false !== $l_key ) {
			unset( $level_array[ $l_key ] );
		}

		return $level_array;
	}

	/**
	 * Grab levels that are counted as "single-use trial membership levels"
	 *
	 * @param array $level_array Array of level IDs (one or more).
	 *
	 * @return int[]             Array of level ID(s).
	 */
	public function get_trial_levels( $level_array = array() ) {

		$this->options = get_option( 'e20rsut_settings', false );

		if (
			function_exists( 'pmpro_isLevelFree' ) &&
			true === (bool) apply_filters( 'e20r_all_free_levels_are_single_use_trials', false )
		) {

			$all_levels = pmpro_getAllLevels( true, true );

			// Add all free levels (trials?) to the filter array
			foreach ( $all_levels as $level ) {
				$this->trial_level_array = $this->set_trial_levels( $level_array, $level );
			}
		} else {

			foreach ( $this->options as $level_id => $is_sut ) {
				if ( false !== $is_sut && ! in_array( $level_id, $level_array, true ) ) {
					$this->trial_level_array[] = $level_id;
				}
			}
		}

		return $this->trial_level_array;
	}

}
