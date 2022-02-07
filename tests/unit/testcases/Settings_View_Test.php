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

namespace E20R\Tests\Unit;

use Codeception\Test\Unit;
use E20R\SingleUseTrial\Settings;
use E20R\SingleUseTrial\Views\Settings_View;
use Brain\Monkey;
use Brain\Monkey\Functions;
use E20R\Utilities\Utilities;

/**
 * Tests for the Settings_View() class
 */
class Settings_View_Test extends Unit {

	/**
	 * Mocked instance of the Utilities class
	 *
	 * @var Utilities|\PHPUnit\Framework\MockObject\MockObject
	 */
	private $m_utils;

	/**
	 * Mocked instance of the Utilities class
	 *
	 * @var Settings|\PHPUnit\Framework\MockObject\MockObject
	 */
	private $m_settings;

	/**
	 * Test setUp
	 */
	public function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		Functions\when( 'plugin_dir_path' )
			->justReturn( '/var/www/html/wp-content/plugins/e20r-single-use-trial/' );

		$this->m_utils = $this->makeEmpty(
			Utilities::class,
			array(
				'log'           => function( $msg ) {
					error_log( "Mocked: ${$msg}" ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				},
				'add_message'   => function( $msg, $sev, $loc ) {
					error_log( "Mocked: '${$msg}' at '{$sev}' in '{$loc}'" ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				},
				'get_client_ip' => null,
			)
		);
	}

	/**
	 * Test the show settings function which generates HTML
	 *
	 * @param int     $level_id      Level ID we're "editing"
	 * @param boolean $exists_status The status we expect
	 * @param array   $options       The settings we'll use
	 * @param string  $checked_text  The returned HTML we expect
	 *
	 * @dataProvider fixture_show_settings
	 *
	 * @test
	 * @throws \Exception When expectation has a problem
	 */
	public function it_checks_html_contains_expected_checkbox_setting( $level_id, $exists_status, $options, $checked_text ) {

		$this->m_settings = $this->makeEmpty( Settings::class );

		$mock_list = array(
			'plugin_dir_path' => __DIR__ . '/../e20r-single-use-trial/',
			'get_option'      => $options,
		);

		// Set the expected Level ID we're configuring for
		$_REQUEST['edit'] = $level_id;

		// Make sure the pmpro_getLevel function 'exists'
		if ( true === $exists_status ) {
			$mock_list['pmpro_getLevel'] = $exists_status;
		}

		Functions\stubs( $mock_list );

		try {
			Functions\expect( 'esc_attr__' )
				->zeroOrMoreTimes()
				->andReturnFirstArg();
		} catch ( \Exception $e ) {
			$this->fail( sprintf( 'Error while processing esc_attr__(): %1$s', $e->getMessage() ) );
		}

		try {
			Functions\expect( 'checked' )
				->atLeast()
				->once()
				->andReturnUsing(
					function ( $request, $current, $echo ) {
						return $request === $current ? 'checked="checked"' : '';
					}
				);

		} catch ( \Exception $e ) {
			$this->fail( sprintf( 'Error while processing checked(): %1$s', $e->getMessage() ) );
		}

		$title          = 'Single Use Trial Settings';
		$label_text     = 'Limit sign-ups to single use?';
		$string_to_find =
			sprintf(
				"<input type='checkbox' name='e20r-single-use-trial' id='e20r-single-use-trial' value='1' %s>",
				$checked_text
			);

		$view        = new Settings_View( $this->m_settings, $this->m_utils );
		$test_result = $view->level( null, $level_id );

		self::assertStringContainsString( $label_text, $test_result );
		self::assertStringContainsString( $title, $test_result );
		self::assertStringContainsString( $string_to_find, $test_result );
	}

	/**
	 * Fixture for the test_e20r_single_use_trial_settings() unit test
	 *
	 * @return array[]
	 */
	public function fixture_show_settings() {
		return array(
			// level_id, exists_status, options, checked_text
			array( 2, false, self::fixture_level_settings(), '' ),
			array( 1, true, self::fixture_level_settings(), 'checked="checked"' ),
			array( 2, true, self::fixture_level_settings(), '' ),
			array( 3, true, self::fixture_level_settings(), '' ),
			array( 4, true, self::fixture_level_settings(), 'checked="checked"' ),
		);
	}

	/**
	 * Provides level settings array for other fixtures
	 *
	 * @return array
	 */
	public static function fixture_level_settings() {
		return array(
			'1' => true,
			'2' => false,
			'3' => false,
			'4' => true,
		);
	}
}
