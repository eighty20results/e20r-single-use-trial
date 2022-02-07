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

use E20R\Utilities\Utilities;
use E20R\Utilities\Message;
use E20R\Utilities\ActivateUtilitiesPlugin;
use E20R\SingleUseTrial\Views\Settings_View;

require_once __DIR__ . '/inc/autoload.php';

if ( ! defined( 'ABSPATH' ) && ! defined( 'PLUGIN_PHPUNIT' ) ) {
	die( 'WordPress not loaded. Naughty, naughty!' );
}

if ( ! defined( 'E20R_SINGLE_USE_TRIAL_VER' ) ) {
	define( 'E20R_SINGLE_USE_TRIAL_VER', '3.0' );
}

if ( ! class_exists( 'E20R\SingleUseTrial\SingleUseTrial' ) ) {
	/**
	 * The primary Single Use Trial Membership class
	 */
	class SingleUseTrial {

		/**
		 * Instance of the Utilities class
		 *
		 * @var null
		 */
		private $utils = null;

		/**
		 * Settings for the SingeUseTrial\Main class
		 *
		 * @var Settings|null
		 */
		private $settings = null;

		/**
		 * The class instance for the Settings View
		 *
		 * @var Settings_View|null $settings_view
		 */
		private $settings_view = null;

		/**
		 * The suspected IP address for the client computer (connected computer).
		 * NOTE: This is often wrong, so we do not include this in the checks by default
		 *
		 * @var null|string $client_ip
		 */
		private $client_ip = null;

		/**
		 *  Configuration section on the Membership Levels page (in settings).
		 *
		 * @param Settings|null      $settings      Instance of the Settings class
		 * @param Settings_View|null $settings_view Instance of the Settings_View() class
		 * @param Utilities|null     $utils         Instance of the Utilities class
		 * @param false|int[]        $options       The configured options
		 *
		 * @uses $_REQUEST['edit']
		 */
		public function __construct( $settings = null, $settings_view = null, $utils = null, $options = false ) {

			if ( empty( $utils ) ) {
				$message = new Message();
				$utils   = new Utilities( $message );
			}

			$this->utils = $utils;

			if ( empty( $settings_view ) ) {
				$settings_view = new Settings_View( $this->utils );
			}

			$this->settings_view = $settings_view;

			if ( empty( $settings ) ) {
				$settings = new Settings( $this->settings_view, $this->utils, $options );
			}

			$this->settings = $settings;
		}

		/**
		 * Load the action and filter handlers used by this plugin
		 *
		 * @return void
		 */
		public function load_hooks() {

			// Don't do anything unless PMPro is loaded
			if ( ! function_exists( 'pmpro_isLevelFree' ) ) {
				$this->utils->add_message(
					esc_attr__( 'Please install and/or activate the Paid Memberships Pro plugin!', 'e20r-single-use-trial' ),
					'error',
					'backend'
				);
				return;
			}

			// Load action hooks
			add_action( 'init', array( $this, 'load_text_domain' ), 1 );

			add_action(
				'pmpro_membership_level_after_other_settings',
				array( $this->settings, 'load_view' ),
				10
			);
			add_action(
				'pmpro_save_membership_level',
				array( $this->settings, 'save' ),
				99
			);
			add_action(
				'pmpro_after_change_membership_level',
				array( $this, 'after_change_membership_level' ),
				10,
				2
			);

			// Load filter hooks
			add_filter(
				'e20r_set_single_use_trial_level_ids',
				array( $this->settings, 'get_trial_levels' ),
				1,
				1
			);

			add_filter(
				'pmpro_registration_checks',
				array( $this, 'pmpro_registration_checks' ),
				99999
			);
		}

		/**
		 * During checkout (for renewals), verify if the user has consumed their trial already.
		 *
		 * @param bool $value The current registration check value as we process the filter(s)
		 *
		 * @return bool
		 */
		public function pmpro_registration_checks( $value ) {

			// Skip if we're not logged in - so not a renewal
			if ( function_exists( 'is_user_logged_in' ) && false === \is_user_logged_in() ) {
				return $value;
			}

			/** List of levels with trial policy set */
			$trial_levels = apply_filters( 'e20r_set_single_use_trial_level_ids', array() );
			$use_ip_addr  = apply_filters( 'e20r_set_single_use_trial_use_ip', false );
			$level_id     = $this->utils->get_variable( 'level', null );
			$user         = \wp_get_current_user();
			$set_warning  = false;

			if ( isset( $user->ID ) && true === $use_ip_addr ) {
				$user_ip      = $this->utils->get_client_ip();
				$user_ip_list = get_user_meta( $user->ID, "e20r_trial_level_{$level_id}_ip_addrs", array() );
				$set_warning  = in_array( $user_ip, $user_ip_list, true );
			}

			if ( isset( $user->ID ) && in_array( $level_id, $trial_levels, true ) ) {
				// Does the currently logged-in user have a trial level they've used
				$set_warning = \get_user_meta( $user->ID, "e20r_trial_level_{$level_id}_used", true );
			}

			if ( true === $set_warning ) {
				$msg = \esc_attr__(
					'You have already used your trial membership. Please select a different membership to checkout.',
					'e20r-single-use-trial'
				);
				pmpro_setMessage( $msg, 'error' );
				$value = false;
			}

			return $value;
		}

		/**
		 * Record that a user checked out for a trial membership
		 *
		 * @param int $level_id The PMPro Membership Level ID
		 * @param int $user_id  The WordPress User's ID
		 */
		public function after_change_membership_level( $level_id, $user_id ) {

			// Level(s) where we limit sign-up to one for a given user ID
			$trial_levels = apply_filters( 'e20r_set_single_use_trial_level_ids', array() );

			if ( in_array( $level_id, $trial_levels, true ) ) {
				// Usermeta is used to record the user's previous trial (free) membership level(s)
				update_user_meta( $user_id, "e20r_trial_level_{$level_id}_used", true );
			}
		}

		/**
		 * Load translation (I18N) file(s) if applicable
		 */
		public function load_text_domain() {

			$locale  = apply_filters( 'plugin_locale', get_locale(), 'e20r-single-use-trial' );
			$mo_file = "e20r-single-use-trial-{$locale}.mo";

			// Path(s) to local and global (WP).
			$mo_file_local  = dirname( __FILE__ ) . "/languages/{$mo_file}";
			$mo_file_global = WP_LANG_DIR . "/e20r-single-use-trial/{$mo_file}";

			// Start with the global file.
			if ( file_exists( $mo_file_global ) ) {

				load_textdomain(
					'e20r-single-use-trial',
					$mo_file_global
				);
			}

			// Load from local next (if applicable).
			load_textdomain(
				'e20r-single-use-trial',
				$mo_file_local
			);

			// Load with plugin_textdomain or GlotPress.
			load_plugin_textdomain(
				'e20r-single-use-trial',
				false,
				dirname( __FILE__ ) . '/languages/'
			);
		}
	}
}

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

/**
 * Load the required E20R Utilities Module functionality
 */
require_once __DIR__ . '/ActivateUtilitiesPlugin.php';

if ( function_exists( 'apply_filters' ) && false === apply_filters( 'e20r_utilities_module_installed', false ) ) {

	$required_plugin = 'E20R Single Use Trial Membership for Paid Memberships Pro';

	if ( false === ActivateUtilitiesPlugin::attempt_activation() ) {
		add_action(
			'admin_notices',
			function () use ( $required_plugin ) {
				ActivateUtilitiesPlugin::plugin_not_installed( $required_plugin );
			}
		);

		return false;
	}
}

if ( function_exists( 'add_action' ) ) {
	$e20r_single_use_trial = new SingleUseTrial();
	add_action( 'plugins_loaded', array( $e20r_single_use_trial, 'load_hooks' ) );
}

if ( class_exists( 'E20R\Utilities\Utilities' ) && defined( 'ABSPATH' ) ) {
	Utilities::configureUpdateServerV4(
		'e20r-single-use-trial',
		__DIR__ . '/e20r-single-use-trial.php'
	);
}
