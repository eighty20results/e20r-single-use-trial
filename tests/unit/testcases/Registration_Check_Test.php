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
use E20R\SingleUseTrial;

use Faker\Generator;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Brain\Monkey;
use Brain\Monkey\Functions;
use function Brain\faker;
use function Brain\Monkey\Filters\expectApplied;

/**
 * Test registration checks
 */
class Registration_Check_Test extends Unit {
	use MockeryPHPUnitIntegration;

	/**
	 * The faker for WP Users, etc
	 *
	 * @var mixed
	 */
	private $wp_faker = null;

	/**
	 * Test setUp
	 */
	public function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		Functions\when( 'plugin_dir_path' )
			->justReturn( '/var/www/html/wp-content/plugins/e20r-single-use-trial/' );

		$faker          = faker();
		$this->wp_faker = $faker->wp();
	}

	/**
	 * Test the registration check when user isn't logged in
	 *
	 * @param bool $value The received registration check
	 * @param bool $expected The expected outcome after the registration check was executed
	 *
	 * @dataProvider notLoggedInFixture
	 * @test
	 */
	public function it_runs_the_registration_check_without_having_logged_in_first( $value, $expected ) {
		Functions\stubs(
			array(
				'is_user_logged_in' => false,
				'plugin_dir_path'   => '/var/www/html/wp-content/plugins/e20r-single-use-trial/',
				'get_option'        => false,
			)
		);

		$sut    = new SingleUseTrial\SingleUseTrial();
		$result = $sut->pmpro_registration_checks( $value );
		self::assertEquals( $expected, $result );
	}

	/**
	 * Test the registration check when the user is logged in
	 *
	 * @param bool  $default Received value from another filter (default)
	 * @param int   $level_id The membership level ID we're "checking out" for
	 * @param int   $user_id The User's ID (WP_User->ID)
	 * @param bool  $trial_is_used Whether we're running the check for a trial level
	 * @param int[] $trial_levels The list of trial levels
	 * @param bool  $expected The expected check result
	 *
	 * @dataProvider loggedInFixture
	 * @test
	 */
	public function it_tests_if_registration_checks_work_for_logged_in_user(
		$default,
		$level_id,
		$user_id,
		$trial_is_used,
		$trial_levels,
		$expected
	) {

		/**
		 * Mock the WordPress apply_filters() function
		 */
		try {
			expectApplied( 'e20r_set_single_use_trial_level_ids' )
				->with( array() )
				->andReturn( $trial_levels );

			expectApplied( 'e20r-licensing-text-domain' )
				->with( null )
				->andReturn( 'e20r-single-use-trial' );

		} catch ( \Exception $e ) {
			printf( "Unexpected trial levels filter call supplied\n" );
		}

		Monkey\Functions\stubs(
			array(
				'get_current_blog_id' => 0,
				'is_user_logged_in'   => true,
				'plugins_url'         => 'http://docker.local/wp-content/plugins/e20r-single-use-trial/',
				'plugin_dir_path'     => '/var/www/html/wp-content/plugins/e20r-single-use-trial/',
				'get_user_meta'       => $trial_is_used,
				'wp_get_current_user' => $this->wp_faker->user( array( 'ID' => $user_id ) ),
				'get_option'          => false,
				'esc_attr__'          => null,
				'pmpro_setMessage'    => function( $msg, $severity ) {
					error_log( "Mocked: {$msg} at {$severity}" ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				},
			)
		);

		global $_REQUEST;

		$_REQUEST = $this->requestFixture( 'level', $level_id );
		$sut      = new SingleUseTrial\SingleUseTrial();
		$result   = $sut->pmpro_registration_checks( $default );
		self::assertEquals( $expected, $result );
	}

	/**
	 * Fixture generating a mock $_REQUEST (global) value
	 *
	 * @param string $key The key to use
	 * @param mixed  $value The value supplied
	 *
	 * @return array
	 */
	public function requestFixture( $key, $value ) {
		return array( $key => $value );
	}

	/**
	 * Fixture for the registration check when a user isn't logged in
	 *
	 * @return array
	 */
	public function notLoggedInFixture() {
		return array(
			array( true, true ),
			array( false, false ),
		);
	}

	/**
	 * Fixture for the registration check when the user is logged in
	 *
	 * @return array[]
	 */
	public function loggedInFixture() {
		return array(
			/** Default, level_id, user_id, trial_is_used, trial_levels, expected */
			array( true, 1, 1000, false, array( 1, 2, 4 ), true ),
			array( true, 1, 1000, true, array( 1, 2, 4 ), false ),
			array( true, 10, 1000, true, array( 1, 2, 4 ), true ), // Because the level ID isn't defined as a trial level
			array( true, 65535, 1000, true, array( 1, 2, 4 ), true ), // Because the level ID isn't defined as a trial level
			array( true, 2, 1000, true, array( 1, 2, 4 ), false ),
			array( true, 2, 1000, false, array( 1, 2, 4 ), true ),
			array( true, 3, 1000, true, array( 1, 2, 4 ), true ),
			array( true, 3, 1000, false, array( 1, 2, 4 ), true ),
			array( true, 4, 1000, true, array( 1, 2, 4 ), false ),
			array( true, 4, 1000, false, array( 1, 2, 4 ), true ),
			array( true, 5, 1000, true, array( 1, 2, 4 ), true ),
			array( true, 5, 1000, false, array( 1, 2, 4 ), true ),

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
