<?php
/**
 * TimeHelperTest file
 *
 * CakePHP(tm) Tests <https://book.cakephp.org/2.0/en/development/testing.html>
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://book.cakephp.org/2.0/en/development/testing.html CakePHP(tm) Tests
 * @package       Cake.Test.Case.View.Helper
 * @since         CakePHP(tm) v 1.2.0.4206
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

App::uses('TimeHelper', 'View/Helper');
App::uses('View', 'View');
App::uses('CakeTime', 'Utility');

/**
 * TimeHelperTestObject class
 *
 * @package       Cake.Test.Case.View.Helper
 */
class TimeHelperTestObject extends TimeHelper {

	public function attach(CakeTimeMock $cakeTime) {
		$this->_engine = $cakeTime;
	}

	public function engine() {
		return $this->_engine;
	}

}

/**
 * CakeTimeMock class
 *
 * @package       Cake.Test.Case.View.Helper
 */
class CakeTimeMock {
}

/**
 * TimeHelperTest class
 *
 * @property View $View
 * @package       Cake.Test.Case.View.Helper
 */
class TimeHelperTest extends CakeTestCase {

	public $Time = null;

	public $CakeTime = null;

/**
 * setUp method
 *
 * @return void
 */
	public function setUp(): void {
		parent::setUp();
		$this->View = new View(null);
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown(): void {
		unset($this->View);
		parent::tearDown();
	}

/**
 * test CakeTime class methods are called correctly
 *
 * @return void
 */
	public function testTimeHelperProxyMethodCalls() {
		$methods = array(
			'convertSpecifiers', 'convert', 'serverOffset', 'fromString',
			'daysAsSql', 'dayAsSql',
			'isToday', 'isThisMonth', 'isThisYear', 'wasYesterday',
			'isTomorrow', 'toQuarter', 'toUnix', 'toAtom', 'toRSS',
			'wasWithinLast', 'gmt', 'format', 'i18nFormat',
		);
		$CakeTime = $this->getMock('CakeTimeMock', $methods);
		$Time = new TimeHelperTestObject($this->View, array('engine' => 'CakeTimeMock'));
		$Time->attach($CakeTime);
		foreach ($methods as $method) {
			$CakeTime->expects($this->at(0))->method($method);
			$Time->{$method}('who', 'what', 'when', 'where', 'how');
		}

		$Time = new TimeHelperTestObject($this->View, array('engine' => 'CakeTimeMock'));
		$Time->attach($CakeTime);
	}

/**
 * test engine override
 *
 * @return void
 */
	public function testEngineOverride() {
		App::build(array(
			'Utility' => array(CAKE . 'Test' . DS . 'test_app' . DS . 'Utility' . DS)
		), App::REGISTER);
		$Time = new TimeHelperTestObject($this->View, array('engine' => 'TestAppEngine'));
		$this->assertInstanceOf('TestAppEngine', $Time->engine());

		App::build(array(
			'Plugin' => array(CAKE . 'Test' . DS . 'test_app' . DS . 'Plugin' . DS)
		));
		CakePlugin::load('TestPlugin');
		$Time = new TimeHelperTestObject($this->View, array('engine' => 'TestPlugin.TestPluginEngine'));
		$this->assertInstanceOf('TestPluginEngine', $Time->engine());
		CakePlugin::unload('TestPlugin');
	}

}
