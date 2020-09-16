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

class test_OptionManagement extends TestCase {
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
	 * Test the save option operation
	 *
	 * @param $exists_status
	 * @param $options
	 * @param $level_id
	 * @param $expected
	 *
	 * @dataProvider save_settings_fixture
	 *
	 * @covers  E20R\SingleUseTrial\e20r_registration_checks
	 */
	public function test_e20r_single_use_trial_settings($exists_status, $options, $level_id, $expected) {

		Brain\Monkey\Functions\stubs( [
				'function_exists' => $exists_status,
				'plugin_dir_path' => __DIR__ . "/../../"
			]
		);

		// get_option is called with the specific option(s) for this plugin
		Brain\Monkey\Functions\expect( 'get_option' )
			->once() // called once
			->with( 'e20rsut_settings', $options, false ) // with specified arguments, like get_option( 'plugin-settings', [] );
			->andReturn( [] ); // what it should return?

		// update_option is called with the specific option(s) for this plugin
        Brain\Monkey\Functions\expect( 'update_option' )
			->once() // called only once
			->with( 'e20rsut_settings', $options, false )
			->andReturn( $expected );

		$result = SUT\e20r_save_single_use_trial( 'e20rsut_settings', $options, false )

		self::assertEquals( $expected, $result );
	}

	public function save_settings_fixture() {

		return(array(
			array( false, null, false),
			array( true, )
		))
	}
}
