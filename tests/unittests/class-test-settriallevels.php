<?php
/*
 * Copyright (c) 2016 - 2020. - Eighty / 20 Results by Wicked Strong Chicks <thomas@eighty20results.com>. ALL RIGHTS RESERVED
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

use E20R\SingleUseTrial as SUT;
use PHPUnit\Framework\TestCase;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Brain\Monkey;

class test_SetTrialLevels extends TestCase {
	use MockeryPHPUnitIntegration;

	/**
	 * Test setUp
	 */
	public function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		Brain\Monkey\Functions\when('plugin_dir_path')
			->justReturn( __DIR__ . "/../../" );

		require_once __DIR__ . '/../../e20r-single-use-trial.php';
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
	 * @param int[] $level_array
	 * @param array $expected
	 * @param int   $count
	 *
	 * @dataProvider createDataSpecificLevelsAreFree
	 *
	 * @covers E20R\SingleUseTrial\e20r_get_trial_levels
	 */
	public function test_e20r_set_trial_levels_specific_levels_are_free($level_array, $expected, $count ) {
		/**
		 * Mocked the get_option WP function
		 */
		Monkey\Functions\when('get_option')
			->alias(function( $key, $default ) {
				$settings = array();
				if ($key != 'e20rsut_settings' ) {
					print("Unexpected key: " . $key );
					return $settings;
				}
				/**
				 * Make the level ID be a trial limited level if it's ID is an odd number
				 */
				foreach ( range(1,5) as $level_id ) {
					$settings[$level_id] = (($level_id % 2) == 0);
				}

				return $settings;
			});

		Monkey\Filters\doing('e20r_all_free_levels_are_single_use_trials' );

		$trial_levels = SUT\e20r_get_trial_levels($level_array);

		foreach( $expected as $exp_key => $exp_value ) {
			self::assertArrayHasKey( $exp_key, $trial_levels );
			self::assertEquals( $expected[$exp_key], $trial_levels[$exp_key] );
			self::assertCount( $count, $trial_levels );
		}
	}

	/**
	 * Exercise code when all free membership level level(s) are configured for single-use trials
	 *
	 * @param int[] $level_array
	 * @param int[] $expected
	 * @param int $count
	 *
	 * @dataProvider createDataAllFreeAreSingleUse
	 *
	 * @covers E20R\SingleUseTrial\e20r_get_trial_levels
	 */
	public function test_e20r_set_trial_levels_all_free_are_single_use($level_array, $expected, $count) {

//		Brain\Monkey\Functions\stubs( [
//				'plugin_dir_path' => __DIR__ . "/../../"
//			]
//		);

		/**
		 * Mock the WordPress apply_filters() function
		 */
		try {
			// TODO: Number of invocations should be added ->once()
			Monkey\Filters\expectApplied( 'e20r_all_free_levels_are_single_use_trials' )
                ->with( false )
                ->andReturn( true );

			// TODO: Number of invocations should be added ->once()
            Monkey\Filters\expectApplied( 'e20r-licensing-text-domain' )
                ->with( null )
				->andReturn( 'e20r-single-use-trial' );

		} catch (\Exception $e) {
			print "Unexpected filter call used\n";
		}
		/**
		 * Mocked the pmpro_getAllLevels PMPro function
		 */
		Monkey\Functions\when('pmpro_getAllLevels')
			->alias(function( $include_hidden, $force ) {
				$levels = array();
				$end_value = $include_hidden ? 5 : 4;
				foreach ( range(1,$end_value) as $level_id ) {
					$levels[$level_id] = $this->fixture_LevelInfo($level_id);
				}
				return $levels;
			});

		Monkey\Functions\when('pmpro_isLevelFree' )
			->alias(function( $level ) {
					return $this->fixture_LevelIsFree( $level->id );
				}
		);

		$trial_levels = SUT\e20r_get_trial_levels($level_array);

		if (count($trial_levels) > 0) {
			foreach ( $expected as $exp_key => $exp_value ) {
				self::assertTrue( in_array( $expected[ $exp_key ], $trial_levels ) );
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
			array( array(1,3), array(), 0 ),
			array( array(5), array(2, 4, 5 ), 3 ),
			array( array(2, 5), array(2, 4, 5 ), 3 ),
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
			array( array(1,2), array( 1, 2, 4 ), 3 )
		);
	}

	/**
	 * Fixture determines if the level ID specified is a free membership level
	 *
	 * @param int $level_id
	 *
	 * @return bool
	 */
	public function fixture_LevelIsFree( $level_id ) {
		/** Level ID => Is it a free-use level */
		$level_info = array( 1 => false, 2 => true, 3 => false, 4 => true, 5 => true);
		return $level_info[$level_id];
	}

	/**
	 * Generate membership level fixture
	 *
	 * @param int $level_id
	 *
	 * @return stdClass
	 */
	public function fixture_LevelInfo($level_id) {
		$signup_choices = array(1, 0);
		$price_choices = array( '0.00', '1.00');

		$level = new \stdClass();
		$level->id = $level_id;
		$level->name = "Test Level " . $level_id;
		$level->description = "Description for test level " . $level_id;
		$level->confirmation = "Confirmation message for test level " . $level_id;
		$level->cycle_number = 0;
		$level->cycle_period = 'Month';
		$level->billing_limit = null;
		$level->trial_limit = '0';
		$level->expiration_number = 0;
		$level->expiration_period = 'Month';
		$level->initial_payment = '0.00';
		$level->billing_amount = array_rand($price_choices);
		$level->trial_amount = '0.00';
		$level->allow_signups = array_rand($signup_choices);

		return $level;
	}
}
