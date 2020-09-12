<?php

class AcceptanceTester extends \Codeception\Actor
{
    // do not ever remove this line!
	use _generated\AcceptanceTesterActions;

	private $I = null;

	/**
	 *
	 * @param $scenario
	 */
	public function __construct($scenario) {
		self::$I = new AcceptanceTester($scenario);
	}

	/**
	 * Test scenario:
	 *
	 * Test description:
	 */
	public function activatePlugin() {

	}

	/**
	 * Test scenario:
	 *
	 * Test description:
	 */
	public function settingsAreOnLevelsPage() {

	}

	/**
	 * Test scenario:
	 *
	 * Test description:
	 */
	public function deactivatePlugin() {

	}

}
