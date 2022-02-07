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

use E20R\SingleUseTrial;
use Codeception\Test\Unit;
use E20R\Utilities\Utilities;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Brain\Monkey;
use Brain\Monkey\Functions;
use Brain\Monkey\Filters;

use E20R\SingleUseTrial\Views\Settings_View;
use E20R\SingleUseTrial\Settings;

class Settings_Test extends Unit {

	use MockeryPHPUnitIntegration;

	private $m_view = null;

	private $m_utils = null;

	private $m_settings = null;

	/**
	 * Test setUp
	 */
	public function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		Functions\when( 'plugin_dir_path' )
			->justReturn( '/var/www/html/wp-content/plugins/e20r-single-use-trial' );

		$this->m_utils = $this->makeEmpty(
			Utilities::class,
			array(
				'log'           => null,
				'add_message'   => null,
				'get_client_ip' => null,
			)
		);

		$this->m_view = $this->makeEmpty(
			Settings_View::class
		);
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
	 * @param array $options The supplied options
	 * @param int   $level_id The level ID we're saving for
	 * @param bool  $expected The expected return value from update_option()
	 *
	 * @dataProvider fixture_save_options
	 *
	 * @test
	 */
	public function it_saves_the_single_use_trial_settings( $options, $level_id, $expected ) {

		$mock_list = array(
			'plugin_dir_path'     => '/var/www/html/wp-content/plugins/e20r-single-use-trial/',
			'plugins_url'         => 'http://localhost.local:7353/wp-content/plugins/',
			'get_current_blog_id' => 0,
			'is_email'            => false,
		);

		Functions\stubs( $mock_list );

		// get_option is called with the specific option(s) for this plugin
		try {
			Functions\expect( 'get_option' )
				// with specified arguments, like get_option( 'plugin-settings', false );
				->with( \Mockery::type( 'string' ), \Mockery::type( 'mixed' ) )
				->atLeast()
				->once()
				->andReturnUsing(
					function( $settings ) use ( $options ) {
						return $settings;
					}
				); // what it should return?
		} catch ( \Exception $e ) {
			$this->fail( sprintf( 'Error during mocked get_option() call: %1$s', $e->getMessage() ) );
		}

		// update_option is called with the specific option(s) for this plugin
		try {
			Functions\expect( 'update_option' )
				->zeroOrMoreTimes()
				->with( \Mockery::type( 'string' ), \Mockery::type( 'array' ), \Mockery::type( 'string' ) )
				->andReturnUsing(
					function( $option_name, $settings, $to_echo ) {
						return 'e20rsut_settings' === $option_name;
					}
				);

		} catch ( \Exception $e ) {
			$this->fail( sprintf( 'Error during mocked update_option() call: %1$s', $e->getMessage() ) );
		}

		// Set the "request" variable so it can be tested
		$_REQUEST['e20r-single-use-trial'] = $options[ $level_id ] ?? null;
		$settings                          = new Settings( $this->m_view, $this->m_utils );
		$result                            = $settings->save( $level_id );

		self::assertEquals( $expected, $result );
	}

	/**
	 * Fixture for the it_saves_the_single_use_trial_settings() unit test
	 *
	 * @return array[]
	 */
	public function fixture_save_options() {
		return (
			array(
				// options, level_id, expected
				array(
					array(
						'1' => false,
						'2' => true,
						'3' => false,
					),
					1,
					true,
				), // No options defined (yet)
				array( Settings_ViewTest::fixture_level_settings(), 2, true ), // Has options defined and using level ID 2
				array( Settings_ViewTest::fixture_level_settings(), 1, true ), // Has options defined and using level ID 2
				array( Settings_ViewTest::fixture_level_settings(), 0, false ), // Not a valid membership level ID
			)
		);
	}
}
