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

namespace E20R\Tests\API;

use ApiTester;
use E20R\Licensing\Settings\LicenseSettings;
use E20R\Licensing\Licensing;
use Helper\Api;

class VerifyLicenseCest {

	private $activation_id = null;
	const LICENSE_KEY      = '7rMmTFJDjeTZyDfB83CwD9x0Mr-193';

	public function _before( ApiTester $i ) { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
		require_once __DIR__ . '/../../src/E20R/Licensing/License.php';
	}

	/**
	 * @param ApiTester $i
	 *
	 */
	public function license_endpoint_is_active( ApiTester $i ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
		$settings = new LicenseSettings();

		$i->wantTo( 'receive a bad request error - 400' );
		$i->haveHttpHeader( 'accept', 'application/json' );
		$i->haveHttpHeader( 'content-type', 'application/json' );
		$i->sendPost( $settings->get( 'plugin_defaults' )->get( 'server_url' ) . '/validate' );
		$i->seeResponseCodeIs( 400 );
		// License ID: 15956
		// License Key Code: 7rMmTFJDjeTZyDfB83CwD9x0Mr-193
	}

	public function activate_product( ApiTester $i ) {
		$settings = new LicenseSettings();

		$i->wantTo( 'activate the "E20R_TEST_LICENSE_KEY" license key' );
		$i->haveHttpHeader( 'accept', 'application/json' );
		$i->haveHttpHeader( 'content-type', 'application/json' );

		$license_info = array(
			'action'      => 'license_key_activate',
			'store_code'  => $settings->get( 'plugin_defaults' )->get( 'store_code' ),
			'sku'         => 'E20R_TEST_LICENSE',
			'domain'      => 'local',
			'license_key' => self::LICENSE_KEY,
		);
		$i->sendPost( $settings->get( 'plugin_defaults' )->get( 'server_url' ) . '/activate', $license_info );
		$i->seeResponseCodeIs( 200 );
		$i->seeResponseIsJson();
		$response = $i->grabResponse();
		echo "License activation: " . print_r( $response, true );
	}

	public function licensed_product_is_active( ApiTester $i ) {
		$settings = new LicenseSettings();

		$i->wantTo( 'verify the licensed product SKU exists' );
		$i->haveHttpHeader( 'accept', 'application/json' );
		$i->haveHttpHeader( 'content-type', 'application/json' );
		$post_payload = array(
			'action'        => 'license_key_validate',
			'store_code'    => $settings->get( 'plugin_defaults' )->get( 'store_code' ),
			'sku'           => 'E20R_TEST_LICENSE',
			'domain'        => 'local',
			'activation_id' => $this->activation_id,
			'license_key'   => self::LICENSE_KEY,
		);
		$i->sendPost( $settings->get( 'plugin_defaults' )->get( 'server_url' ) . '/validate', $post_payload );
		$i->seeResponseCodeIs( 200 );
		$i->seeResponseIsJson();
		$i->seeResponseContainsJson( array( 'error' => true ) );
		$i->seeResponseContainsJson( array( 'status' => 500 ) );
		$response = $i->grabResponse();
		echo "Response: " . print_r( $response, true );
	}
}
