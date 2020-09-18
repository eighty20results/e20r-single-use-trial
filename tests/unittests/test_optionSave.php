<?php
/*
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

use E20R\SingleUseTrial as SUT;
use PHPUnit\Framework\TestCase;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Brain\Monkey;
use Brain\Faker;
use Faker\Generator;
use E20R\Utilities\Utilities;

class test_OptionSave extends TestCase {
	use MockeryPHPUnitIntegration;

	/**
	 * @var Generator
	 */
	protected $faker;

	/**
	 * @var Faker\Providers
	 */
	protected $wpFaker;

	/**
	 * Test setUp
	 */
	public function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		$this->faker   = Brain\faker();
		$this->wpFaker = $this->faker->wp();

		Brain\Monkey\Functions\when( 'plugin_dir_path' )
			->justReturn( __DIR__ . "/../../" );

		require_once __DIR__ . '/../../e20r-single-use-trial.php';
	}

	/**
	 * Test the save option operation
	 *
	 * @param $options
	 * @param $level_id
	 * @param $expected
	 *
	 * @dataProvider fixture_save_options
	 *
	 * @covers       E20R\SingleUseTrial\e20r_save_single_use_trial
	 */
	public function test_e20r_save_single_use_trial( $options, $level_id, $expected ) {

		$mock_list = array(
			'plugin_dir_path' => __DIR__ . "/../../",
			'is_email' => false,
		);

		Brain\Monkey\Functions\stubs( $mock_list );

		// get_option is called with the specific option(s) for this plugin
		try {
			Brain\Monkey\Functions\expect( 'get_option' )
				// with specified arguments, like get_option( 'plugin-settings', false );
				->with( Mockery::type('string'), Mockery::type('mixed') )
				->atLeast()
				->once()
				->andReturnUsing( function( $settings ) use ($options) {
					return $settings;
				} ); // what it should return?
		} catch ( \Exception $e ) {
			printf( "Error during mocked get_option() call: {$e->getMessage()}\n" );
			self::assertFalse( true );
		}

		// update_option is called with the specific option(s) for this plugin
		try {
			Brain\Monkey\Functions\expect( 'update_option' )
				->zeroOrMoreTimes()
				->with( Mockery::type('string'), Mockery::type('array' ), Mockery::type('string' ) )
				->andReturnUsing( function( $option_name, $settings, $to_echo ) {
					return 'e20rsut_settings' === $option_name;
				} );

		} catch ( \Exception $e ) {
			printf( "Error during mocked update_option() call: {$e->getMessage()}\n" );
			self::assertFalse( true );
		}

		// Set the "request" variable so it can be tested
		$_REQUEST['e20r-single-use-trial'] = isset( $options[ $level_id ] ) ? $options[ $level_id ] : null;
		$result                            = SUT\e20r_save_single_use_trial( $level_id );

		self::assertEquals( $expected, $result );
	}

	/**
	 * Test the show settings function
	 *
	 * @param int     $level_id
	 * @param boolean $exists_status
	 * @param array   $options
	 * @param string  $checked_text
	 *
	 * @dataProvider fixture_show_settings
	 *
	 * @covers       E20R\SingleUseTrial\e20r_single_use_trial_settings
	 */
	public function test_e20r_single_use_trial_settings( $level_id, $exists_status, $options, $checked_text ) {

		$mock_list = array(
			'plugin_dir_path' => __DIR__ . "/../../",
			'get_option'      => $options,
		);

		// Set the expected Level ID we're configuring for
		$_REQUEST['edit'] = $level_id;

		// Make sure the pmpro_getLevel function 'exists'
		if ( true === $exists_status ) {
			$mock_list['pmpro_getLevel'] = $exists_status;
		}

		Brain\Monkey\Functions\stubs( $mock_list );

		try {
			Brain\Monkey\Functions\expect( '__' )
				->zeroOrMoreTimes()
				->andReturnFirstArg();
		} catch ( Exception $e ) {
			print( "Error while processing __(): {$e->getMessage()}" );
			self::assertFalse( true );
		}

		try {
			Brain\Monkey\Functions\expect( 'checked' )
				->atLeast()
				->once()
				->andReturnUsing( function ( $request, $current, $echo ) {
					return $request === $current ? 'checked="checked"' : '';
				} );

		} catch ( Exception $e ) {
			print( "Error while processing checked(): {$e->getMessage()}" );
			self::assertFalse( true );
		}

		$title          = "Single Use Trial Settings";
		$label_text     = "Limit sign-ups to single use?";
		$string_to_find =
			sprintf(
				"<input type='checkbox' name='e20r-single-use-trial' id='e20r-single-use-trial' value='1' %s>",
				$checked_text
			);

		$test_result = SUT\e20r_single_use_trial_settings();

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
			// $level_id, $exists_status, $options, $checked_text
			array( 2, false, $this->fixture_level_settings(), '' ),
			array( 1, true, $this->fixture_level_settings(), 'checked="checked"' ),
			array( 2, true, $this->fixture_level_settings(), '' ),
			array( 3, true, $this->fixture_level_settings(), '' ),
			array( 4, true, $this->fixture_level_settings(), 'checked="checked"' ),
		);
	}

	/**
	 * Provides level settings array for other fixtures
	 *
	 * @return array
	 */
	public function fixture_level_settings() {
		return array(
			'1' => true,
			'2' => false,
			'3' => false,
			'4' => true,
		);
	}

	/**
	 * Fixture for the test_e20r_save_single_use_trial() unit test
	 *
	 * @return array[]
	 */
	public function fixture_save_options() {
		return (
			array(
				// $options, $level_id, $expected
				array( array( '1' => false, '2' => true, '3' => false ), 1, true ), // No options defined (yet)
				array( $this->fixture_level_settings(), 2, true ), // Has options defined and using level ID 2
				array( $this->fixture_level_settings(), 1, true ), // Has options defined and using level ID 2
				array( $this->fixture_level_settings(), 0, false ) // Not a valid membership level ID
			)
		);
	}

	/**
	 * Test tear-down function
	 */
	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}
}
