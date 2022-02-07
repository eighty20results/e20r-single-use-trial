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

namespace E20R\SingleUseTrial\Views;

use E20R\SingleUseTrial\Settings;
use E20R\Utilities\Message;
use E20R\Utilities\Utilities;

/**
 * The View on the PMPro Levels Settings page
 *
 * @class E20R\SingleUseTrial\Views\Settings_View
 */
class Settings_View {

	/**
	 * Instance of the Utilities class
	 *
	 * @var Utilities|mixed|null
	 */
	private $utils = null;

	/**
	 * Instance of the Settings class
	 *
	 * @var Settings|mixed|null
	 */
	private $settings = null;

	/**
	 * The constructor for the Settings_View() class
	 *
	 * @param Settings|null  $settings The Settings() class
	 * @param Utilities|null $utils The Utilities() class
	 */
	public function __construct( $settings = null, $utils = null ) {

		if ( empty( $utils ) ) {
			$message = new Message();
			$utils   = new Utilities( $message );
		}

		$this->utils = $utils;

		if ( empty( $settings ) ) {
			$settings = new Settings( $this, $this->utils );
		}

		$this->settings = $settings;
	}

	/**
	 * Generate the view for the Membership Level Settings
	 *
	 * @param array $level_settings The level configuration
	 * @param int   $level_id The ID of the membership level we're processing/showing settings for
	 *
	 * @return string
	 */
	public function level( $level_settings, $level_id ) {
		$level_html   = sprintf(
			"\t<h3 class='topborder'>%s</h3>\n",
			esc_attr__( 'Single Use Trial Settings', 'e20r-single-use-trial' )
		);
		$level_html  .= sprintf( "\t<p class='e20r-description'>\n" );
		$level_html  .= sprintf( "\t\t<p class='e20r-description'>\n" );
		$level_html  .= sprintf(
			"\t\t\t%s\n</p>\n",
			esc_attr__(
				'Prevent members from signing up for this membership level more than once?',
				'e20r-single-use-trial'
			)
		);
		$level_html  .= sprintf( "\t<table class='form-table'>\n" );
		$level_html  .= sprintf( "\t\t<tbody>\n" );
		$level_html  .= sprintf( "\t\t\t<tr>\n" );
		$level_html  .= sprintf( "\t\t\t\t<th scope='row' valign='top'><label for='e20r-single-use-trial'>\n" );
		$level_html  .= sprintf(
			"\t\t\t\t\t%s\n",
			esc_attr__( 'Limit sign-ups to single use?', 'e20r-single-use-trial' )
		);
		$level_html  .= sprintf( "\t\t\t\t\t</label>\n" );
		$level_html  .= sprintf( "\t\t\t\t</th>\n" );
		$level_html  .= sprintf( "\t\t\t\t<td>\n" );
		$checked_html = ( null !== $level_settings && isset( $level_settings[ $level_id ] ) ) ?
			checked( (bool) $level_settings[ $level_id ], true, true ) :
			null;
		$level_html  .= sprintf(
			"\t\t\t\t\t<input type='checkbox' name='e20r-single-use-trial' id='e20r-single-use-trial' value='1' %s>\n",
			$checked_html
		);
		$level_html  .= sprintf( "\t\t\t\t</td>\n" );
		$level_html  .= sprintf( "\t\t\t</tr>\n" );
		$level_html  .= sprintf( "\t\t</tbody>\n" );
		$level_html  .= sprintf( "\t</table>\n" );

		return $level_html;
	}

	/**
	 * Change the error message text when selecting membership level after the trial has been used
	 *
	 * @param string    $text - The text to show when there is an error
	 * @param \stdClass $level - The membership Level info
	 *
	 * @return string|void
	 *
	 * @filter e20r_set_trial_level_ids
	 */
	public function level_expiration_text( $text, $level ) {

		$user_id      = get_current_user_id();
		$level_id     = $level->id;
		$trial_levels = $this->settings->get_trial_levels();
		$has_used     = (bool) get_user_meta( $user_id, "e20r_trial_level_{$level_id}_used", false );

		if ( empty( $user_id ) ) {
			return $text;
		}

		if ( empty( $has_used ) ) {
			return $text;
		}

		if ( ! in_array( $level_id, $trial_levels, true ) ) {
			return $text;
		}

		// At this point, we should return the expiration text, unless the IP blocker is activated
		if ( true === (bool) apply_filters( 'e20r_set_single_use_trial_use_ip', false ) ) {
			$user_ip_list = get_user_meta( $user_id, "e20r_trial_level_{$level_id}_ip_addrs", array() );
			$user_ip      = $this->utils->get_client_ip();

			// Check whether we already have this user ID in the list of IP addrs to block
			if ( ! in_array( $user_ip, $user_ip_list, true ) ) {
				return $text;
			}
		}

		// Return the notification text
		$text = esc_attr__(
			'You have already used your trial membership. Please select a different membership level.',
			'e20r-single-use-trial'
		);

		return apply_filters( 'e20r_single_use_trial_rejection_text', $text );
	}
}
