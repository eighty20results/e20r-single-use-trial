<?php
/*
 * Copyright (c) 2016 - 2021. - Eighty / 20 Results by Wicked Strong Chicks <thomas@eighty20results.com>. ALL RIGHTS RESERVED
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

namespace E20R\Tests\Unit\TestCase;

use E20R\SingleUseTrial as SUT;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Brain\Monkey;
use Brain\Faker;
use Faker\Generator;

class RegistrationCheckTests extends \Codeception\Test\Unit {
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

		$this->faker = Brain\faker();
		$this->wpFaker = $this->faker->wp();

		Brain\Monkey\Functions\when('plugin_dir_path')
			->justReturn('/var/www/html/wp-content/plugins/e20r-single-use-trial/' );

		require_once __DIR__ . '/../../e20r-single-use-trial.php';
	}

	/**
	 * Test the registration check if user isn't logged in
	 *
	 * @param $value
	 * @param $expected
	 *
	 * @dataProvider notLoggedInFixture
	 *
	 * @covers  E20R\SingleUseTrial\e20r_registration_checks
	 */
	public function test_registration_checks_not_logged_in( $value, $expected ) {
		Brain\Monkey\Functions\stubs( [
				'is_user_logged_in' => false,
				'plugin_dir_path' => '/var/www/html/wp-content/plugins/e20r-single-use-trial/'
			]
		);

		$result = SUT\e20r_registration_checks( $value );
		self::assertEquals( $expected, $result );
	}

	/**
	 * Test the registration check when the user is logged in
	 *
	 * @param bool $default
	 * @param int $level_id
	 * @param int $user_id
	 * @param bool $trial_is_used
	 * @param int[] $trial_levels
	 * @param bool $expected
	 *
	 * @dataProvider loggedInFixture
	 * @covers  E20R\SingleUseTrial\e20r_registration_checks
	 */
	public function test_registration_checks_logged_in( $default, $level_id, $user_id, $trial_is_used, $trial_levels, $expected ) {

		/**
		 * Mock the WordPress apply_filters() function
		 */
		try {
            Monkey\Filters\expectApplied('e20r_set_single_use_trial_level_ids' )
                ->with( array() )
                ->andReturn( $trial_levels );

            Monkey\Filters\expectApplied( 'e20r-licensing-text-domain' )
                ->with( null )
				->andReturn( 'e20r-single-use-trial' );

		} catch ( \Exception $e ) {
			printf("Unexpected trial levels filter call supplied\n");
		}

		Monkey\Functions\stubs(
			[
				'get_current_blog_id' => 0,
				'is_user_logged_in' => true,
				'plugins_url' => "http://docker.local/wp-content/plugins/e20r-single-use-trial/",
				'plugin_dir_path' => '/var/www/html/wp-content/plugins/e20r-single-use-trial/',
				'get_user_meta' => $trial_is_used,
				'wp_get_current_user' => $this->wpFaker->user(array('ID' => $user_id ) ),
				'__' => null,
			]);


		global $_REQUEST;

        $_REQUEST = $this->requestFixture( 'level', $level_id );
		$result = SUT\e20r_registration_checks( $default );
		self::assertEquals( $expected, $result );
	}

	/**
	 * Fixture generating a mock $_REQUEST (global) value
	 *
	 * @param string $key
	 * @param mixed  $value
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
	 * @return array
	 */
	public function loggedInFixture() {
		return array(
			/** default, level_id, user_id, trial_is_used, trial_levels, expected */
			array( true, 1, 1000, false, array(1, 2, 4), true ),
			array( true, 1, 1000, true, array(1, 2, 4), false ),
			array( true, 10, 1000, true, array(1, 2, 4), true ), // Because the level ID isn't defined as a trial level
			array( true, 65535, 1000, true, array(1, 2, 4), true ), // Because the level ID isn't defined as a trial level
			array( true, 2, 1000, true, array(1, 2, 4), false ),
			array( true, 2, 1000, false, array(1, 2, 4), true ),
			array( true, 3, 1000, true, array(1, 2, 4), true ),
			array( true, 3, 1000, false, array(1, 2, 4), true ),
			array( true, 4, 1000, true, array(1, 2, 4), false ),
			array( true, 4, 1000, false, array(1, 2, 4), true ),
			array( true, 5, 1000, true, array(1, 2, 4), true ),
			array( true, 5, 1000, false, array(1, 2, 4), true ),

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
