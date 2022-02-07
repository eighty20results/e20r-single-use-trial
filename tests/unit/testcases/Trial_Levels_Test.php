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
use E20R\SingleUseTrial\SingleUseTrial;
use E20R\SingleUseTrial\Views\Settings_View;
use E20R\Utilities\Utilities;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Brain\Monkey;
use Brain\Monkey\Functions;
use Brain\Monkey\Filters;

/**
 * Tests the get_trial_levels() function and filter(s)
 */
class Trial_Levels_Test extends Unit {
	use MockeryPHPUnitIntegration;

	private $m_view;

	private $m_utils;

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

		$this->m_view = $this->makeEmpty(
			Settings_View::class
		);

		// require_once __DIR__ . '/../../../e20r-single-use-trial.php';
	}

	/**
	 * Test tear-down function
	 */
	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Exercise code when specific levels are free and "trial levels"
	 *
	 * @param int[] $level_array Array of PMPro Membership Level definitions
	 * @param array $expected Expected result
	 * @param int   $count Count of levels we expect to be considered trial levels
	 *
	 * @dataProvider createDataSpecificLevelsAreFree
	 *
	 * @test
	 */
	public function it_makes_sure_trial_levels_are_free( $level_array, $expected, $count ) {
		/**
		 * Mocked the get_option WP function
		 */
		Functions\when( 'get_option' )
			->alias(
				function( $key, $default ) {
					$settings = array();
					if ( 'e20rsut_settings' !== $key ) {
						echo "Unexpected key: {$key}";
						return $settings;
					}
					/**
					 * Make the level ID be a trial limited level if it's ID is an odd number
					 */
					foreach ( range( 1, 5 ) as $level_id ) {
						$settings[ $level_id ] = ( ( $level_id % 2 ) === 0 );
					}

					return $settings;
				}
			);

		Filters\doing( 'e20r_all_free_levels_are_single_use_trials' );
		$settings     = new Settings( $this->m_view, $this->m_utils );
		$trial_levels = $settings->get_trial_levels( $level_array );

		foreach ( $expected as $exp_key => $exp_value ) {
			self::assertArrayHasKey( $exp_key, $trial_levels );
			self::assertEquals( $expected[ $exp_key ], $trial_levels[ $exp_key ] );
			self::assertCount( $count, $trial_levels );
		}
	}

	/**
	 * Exercise code when all free membership level level(s) are configured for single-use trials
	 *
	 * @param int[] $level_array Array of PMPro Level configurations
	 * @param int[] $expected Expected array of level IDs marked as free/single use
	 * @param int   $count Counted number of free levels
	 *
	 * @dataProvider createDataAllFreeAreSingleUse
	 *
	 * @test it_makes_sure_all_free_levels_are_configured_as_single_use
	 */
	public function it_makes_sure_all_free_levels_are_configured_as_single_use( $level_array, $expected, $count ) {

		// Brain\Monkey\Functions\stubs( [
		// 'plugin_dir_path' => __DIR__ . "/../../"
		// ]
		// );

		/**
		 * Mock the WordPress apply_filters() function
		 */
		try {
			// TODO: Number of invocations should be added ->once()
			Filters\expectApplied( 'e20r_all_free_levels_are_single_use_trials' )
				->with( false )
				->once()
				->andReturn( true );

			// TODO: Number of invocations should be added ->once()
			Filters\expectApplied( 'e20r-licensing-text-domain' )
				->with( null )
				->once()
				->andReturn( 'e20r-single-use-trial' );

		} catch ( \Exception $e ) {
			print "Unexpected filter call used\n";
		}
		/**
		 * Mocked the pmpro_getAllLevels PMPro function
		 */
		Functions\when( 'pmpro_getAllLevels' )
			->alias(
				function( $include_hidden, $force ) {
					$levels    = array();
					$end_value = $include_hidden ? 5 : 4;
					foreach ( range( 1, $end_value ) as $level_id ) {
						$levels[ $level_id ] = $this->fixture_LevelInfo( $level_id );
					}
					return $levels;
				}
			);

		Functions\when( 'pmpro_isLevelFree' )
			->alias(
				function( $level ) {
					return $this->fixture_LevelIsFree( $level->id );
				}
			);

		Functions\when( 'get_current_blog_id' )
			->justReturn( 1 );

		Functions\when( 'get_option' )
			->alias(
				function( $key, $default ) {
					$settings = array();
					if ( 'e20rsut_settings' !== $key ) {
						echo "Unexpected key: {$key}";
						return $settings;
					}
					/**
					 * Make the level ID be a trial limited level if it's ID is an odd number
					 */
					foreach ( range( 1, 5 ) as $level_id ) {
						$settings[ $level_id ] = ( ( $level_id % 2 ) === 0 );
					}

					return $settings;
				}
			);

		$settings     = new Settings( $this->m_view, $this->m_utils );
		$trial_levels = $settings->get_trial_levels( $level_array );

		if ( count( $trial_levels ) > 0 ) {
			foreach ( $expected as $exp_key => $exp_value ) {
				self::assertTrue( in_array( $exp_value, $trial_levels, true ) );
				self::assertCount( $count, $trial_levels );
			}
		} else {
			self::assertCount( $count, $trial_levels );
		}

		// self::assertTrue( Monkey\Filters\applied('e20r_all_free_levels_are_single_use_trials' ) > 0);
	}

	/**
	 * Parameterized fixture for test_e20r_set_trial_levels_specific tests
	 *
	 * @return array[]
	 */
	public function createDataAllFreeAreSingleUse() {
		return array(
			// Already listed as free, Expected list after processing, count
			array( array(), array(), 0 ),
			array( array(), array( 2, 4, 5 ), 3 ),
			array( array( 1, 3 ), array(), 0 ),
			array( array( 5 ), array( 2, 4, 5 ), 3 ),
			array( array( 2, 5 ), array( 2, 4, 5 ), 3 ),
		);
	}

	/**
	 * Parameterized fixture for test_e20r_set_trial_levels_specific tests
	 *
	 * @return array[]
	 */
	public function createDataSpecificLevelsAreFree() {
		return array(
			// Level IDs, trial_level_id, expected elements,
			array( array(), array( 2, 4 ), 2 ),
			array( array( 1, 2 ), array( 1, 2, 4 ), 3 ),
		);
	}

	/**
	 * Fixture determines if the level ID specified is a free membership level
	 *
	 * @param int $level_id The level ID to set as 'free'
	 *
	 * @return bool
	 */
	public function fixture_LevelIsFree( $level_id ) {
		/** Level ID => Is it a free-use level */
		$level_info = array(
			1 => false,
			2 => true,
			3 => false,
			4 => true,
			5 => true,
		);
		return $level_info[ $level_id ];
	}

	/**
	 * Generate membership level fixture
	 *
	 * @param int $level_id The level ID to provide a PMPro Membership Level config for
	 *
	 * @return \stdClass
	 */
	public function fixture_LevelInfo( $level_id ) {
		$signup_choices = array( 1, 0 );
		$price_choices  = array( '0.00', '1.00' );

		$level                    = new \stdClass();
		$level->id                = $level_id;
		$level->name              = 'Test Level ' . $level_id;
		$level->description       = 'Description for test level ' . $level_id;
		$level->confirmation      = 'Confirmation message for test level ' . $level_id;
		$level->cycle_number      = 0;
		$level->cycle_period      = 'Month';
		$level->billing_limit     = null;
		$level->trial_limit       = '0';
		$level->expiration_number = 0;
		$level->expiration_period = 'Month';
		$level->initial_payment   = '0.00';
		$level->billing_amount    = array_rand( $price_choices );
		$level->trial_amount      = '0.00';
		$level->allow_signups     = array_rand( $signup_choices );

		return $level;
	}
}
