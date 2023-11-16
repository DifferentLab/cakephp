<?php
/**
 * CakeTimeTest file
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

App::uses('CakeTime', 'Utility');

/**
 * CakeTimeTest class
 *
 * @property CakeTime $Time
 * @package       Cake.Test.Case.View.Helper
 */
class CakeTimeTest extends CakeTestCase {

/**
 * Default system timezone identifier
 *
 * @var string
 */
	protected $_systemTimezoneIdentifier = null;

/**
 * setUp method
 *
 * @return void
 */
	public function setUp(): void {
		parent::setUp();
		$this->Time = new CakeTime();
		$this->_systemTimezoneIdentifier = date_default_timezone_get();
		Configure::write('Config.language', 'eng');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown(): void {
		parent::tearDown();
		unset($this->Time);
		$this->_restoreSystemTimezone();
	}

/**
 * Restored the original system timezone
 *
 * @param string $timezoneIdentifier Timezone string
 * @return void
 */
	protected function _restoreSystemTimezone() {
		date_default_timezone_set($this->_systemTimezoneIdentifier);
	}

/**
 * testToQuarter method
 *
 * @return void
 */
	public function testToQuarter() {
		$result = $this->Time->toQuarter('2007-12-25');
		$this->assertSame(4, $result);

		$result = $this->Time->toQuarter('2007-9-25');
		$this->assertSame(3, $result);

		$result = $this->Time->toQuarter('2007-3-25');
		$this->assertSame(1, $result);

		$result = $this->Time->toQuarter('2007-3-25', true);
		$this->assertEquals(array('2007-01-01', '2007-03-31'), $result);

		$result = $this->Time->toQuarter('2007-5-25', true);
		$this->assertEquals(array('2007-04-01', '2007-06-30'), $result);

		$result = $this->Time->toQuarter('2007-8-25', true);
		$this->assertEquals(array('2007-07-01', '2007-09-30'), $result);

		$result = $this->Time->toQuarter('2007-12-25', true);
		$this->assertEquals(array('2007-10-01', '2007-12-31'), $result);
	}

/**
 * testDaysAsSql method
 *
 * @return void
 */
	public function testDaysAsSql() {
		$begin = time();
		$end = time() + DAY;
		$field = 'my_field';
		$expected = '(my_field >= \'' . date('Y-m-d', $begin) . ' 00:00:00\') AND (my_field <= \'' . date('Y-m-d', $end) . ' 23:59:59\')';
		$this->assertEquals($expected, $this->Time->daysAsSql($begin, $end, $field));
	}

/**
 * testDayAsSql method
 *
 * @return void
 */
	public function testDayAsSql() {
		$time = time();
		$field = 'my_field';
		$expected = '(my_field >= \'' . date('Y-m-d', $time) . ' 00:00:00\') AND (my_field <= \'' . date('Y-m-d', $time) . ' 23:59:59\')';
		$this->assertEquals($expected, $this->Time->dayAsSql($time, $field));
	}

/**
 * testToUnix method
 *
 * @return void
 */
	public function testToUnix() {
		$this->assertEquals(time(), $this->Time->toUnix(time()));
		$this->assertEquals(strtotime('+1 day'), $this->Time->toUnix('+1 day'));
		$this->assertEquals(strtotime('+0 days'), $this->Time->toUnix('+0 days'));
		$this->assertEquals(strtotime('-1 days'), $this->Time->toUnix('-1 days'));
		$this->assertEquals(false, $this->Time->toUnix(''));
		$this->assertEquals(false, $this->Time->toUnix(null));
	}

/**
 * testToServer method
 *
 * @return void
 */
	public function testToServer() {
		date_default_timezone_set('Europe/Paris');

		$time = time();
		$this->assertEquals(date('Y-m-d H:i:s', $time), $this->Time->toServer($time));

		date_default_timezone_set('America/New_York');
		$time = time();
		date_default_timezone_set('Europe/Paris');
		$result = $this->Time->toServer($time, 'America/New_York');
		$this->assertEquals(date('Y-m-d H:i:s', $time), $result);

		date_default_timezone_set('Europe/Paris');
		$time = '2005-10-25 10:00:00';
		$result = $this->Time->toServer($time);
		$date = new DateTime($time, new DateTimeZone('UTC'));
		$date->setTimezone(new DateTimeZone(date_default_timezone_get()));
		$expected = $date->format('Y-m-d H:i:s');
		$this->assertEquals($expected, $result);

		$time = '2002-01-01 05:15:30';
		$result = $this->Time->toServer($time, 'America/New_York');
		$date = new DateTime($time, new DateTimeZone('America/New_York'));
		$date->setTimezone(new DateTimeZone(date_default_timezone_get()));
		$expected = $date->format('Y-m-d H:i:s');
		$this->assertEquals($expected, $result);

		$time = '2010-01-28T15:00:00+10:00';
		$result = $this->Time->toServer($time, 'America/New_York');
		$date = new DateTime($time);
		$date->setTimezone(new DateTimeZone(date_default_timezone_get()));
		$expected = $date->format('Y-m-d H:i:s');
		$this->assertEquals($expected, $result);

		$date = new DateTime(null, new DateTimeZone('America/New_York'));
		$result = $this->Time->toServer($date, 'Pacific/Tahiti');
		$date->setTimezone(new DateTimeZone(date_default_timezone_get()));
		$expected = $date->format('Y-m-d H:i:s');
		$this->assertEquals($expected, $result);

		$this->_restoreSystemTimezone();

		$time = time();
		$result = $this->Time->toServer($time, null, 'l jS \of F Y h:i:s A');
		$expected = date('l jS \of F Y h:i:s A', $time);
		$this->assertEquals($expected, $result);

		$this->assertFalse($this->Time->toServer(time(), new CakeObject()));

		date_default_timezone_set('UTC');

		$serverTime = new DateTime('2012-12-11 14:15:20');

		$timezones = array('Europe/London', 'Europe/Brussels', 'UTC', 'America/Denver', 'America/Caracas', 'Asia/Kathmandu');
		foreach ($timezones as $timezone) {
			$result = $this->Time->toServer($serverTime->format('Y-m-d H:i:s'), $timezone, 'U');
			$tz = new DateTimeZone($timezone);
			$this->assertEquals($serverTime->format('U'), $result + $tz->getOffset($serverTime));
		}

		date_default_timezone_set('UTC');
		$date = new DateTime('now', new DateTimeZone('America/New_York'));

		$result = $this->Time->toServer($date, null, 'Y-m-d H:i:s');
		$date->setTimezone($this->Time->timezone());
		$expected = $date->format('Y-m-d H:i:s');
		$this->assertEquals($expected, $result);

		$this->_restoreSystemTimezone();
	}

/**
 * testToAtom method
 *
 * @return void
 */
	public function testToAtom() {
		$this->assertEquals(date('Y-m-d\TH:i:s\Z'), $this->Time->toAtom(time()));
	}

/**
 * testToRss method
 *
 * @return void
 */
	public function testToRss() {
		$date = '2012-08-12 12:12:45';
		$time = strtotime($date);
		$this->assertEquals(date('r', $time), $this->Time->toRss($time));

		$timezones = array('Europe/London', 'Europe/Brussels', 'UTC', 'America/Denver', 'America/Caracas', 'Asia/Kathmandu');
		foreach ($timezones as $timezone) {
			$yourTimezone = new DateTimeZone($timezone);
			$yourTime = new DateTime($date, $yourTimezone);
			$userOffset = $yourTimezone->getOffset($yourTime) / HOUR;
			$time = $yourTime->format('U');
			$this->assertEquals($yourTime->format('r'), $this->Time->toRss($time, $userOffset), "Failed on $timezone");
			$this->assertEquals($yourTime->format('r'), $this->Time->toRss($time, $timezone), "Failed on $timezone");
		}
	}

/**
 * testOfGmt method
 *
 * @return void
 */
	public function testGmt() {
		$hour = 3;
		$min = 4;
		$sec = 2;
		$month = 5;
		$day = 14;
		$year = 2007;
		$time = mktime($hour, $min, $sec, $month, $day, $year);
		$expected = gmmktime($hour, $min, $sec, $month, $day, $year);
		$this->assertEquals($expected, $this->Time->gmt(date('Y-n-j G:i:s', $time)));

		$hour = date('H');
		$min = date('i');
		$sec = date('s');
		$month = date('m');
		$day = date('d');
		$year = date('Y');
		$expected = gmmktime($hour, $min, $sec, $month, $day, $year);
		$this->assertEquals($expected, $this->Time->gmt(null));
	}

/**
 * testIsToday method
 *
 * @return void
 */
	public function testIsToday() {
		$result = $this->Time->isToday('+1 day');
		$this->assertFalse($result);
		$result = $this->Time->isToday('+1 days');
		$this->assertFalse($result);
		$result = $this->Time->isToday('+0 day');
		$this->assertTrue($result);
		$result = $this->Time->isToday('-1 day');
		$this->assertFalse($result);
	}

/**
 * testIsFuture method
 *
 * @return void
 */
	public function testIsFuture() {
		$this->assertTrue($this->Time->isFuture('+1 month'));
		$this->assertTrue($this->Time->isFuture('+1 days'));
		$this->assertTrue($this->Time->isFuture('+1 minute'));
		$this->assertTrue($this->Time->isFuture('+1 second'));

		$this->assertFalse($this->Time->isFuture('-1 second'));
		$this->assertFalse($this->Time->isFuture('-1 day'));
		$this->assertFalse($this->Time->isFuture('-1 week'));
		$this->assertFalse($this->Time->isFuture('-1 month'));
	}

/**
 * testIsPast method
 *
 * @return void
 */
	public function testIsPast() {
		$this->assertFalse($this->Time->isPast('+1 month'));
		$this->assertFalse($this->Time->isPast('+1 days'));
		$this->assertFalse($this->Time->isPast('+1 minute'));
		$this->assertFalse($this->Time->isPast('+1 second'));

		$this->assertTrue($this->Time->isPast('-1 second'));
		$this->assertTrue($this->Time->isPast('-1 day'));
		$this->assertTrue($this->Time->isPast('-1 week'));
		$this->assertTrue($this->Time->isPast('-1 month'));
	}

/**
 * testIsThisWeek method
 *
 * @return void
 */
	public function testIsThisWeek() {
		// A map of days which goes from -1 day of week to +1 day of week
		$map = array(
			'Mon' => array(-1, 7), 'Tue' => array(-2, 6), 'Wed' => array(-3, 5),
			'Thu' => array(-4, 4), 'Fri' => array(-5, 3), 'Sat' => array(-6, 2),
			'Sun' => array(-7, 1)
		);
		$days = $map[date('D')];

		for ($day = $days[0] + 1; $day < $days[1]; $day++) {
			$this->assertTrue($this->Time->isThisWeek(($day > 0 ? '+' : '') . $day . ' days'));
		}
		$this->assertFalse($this->Time->isThisWeek($days[0] . ' days'));
		$this->assertFalse($this->Time->isThisWeek('+' . $days[1] . ' days'));
	}

/**
 * testIsThisMonth method
 *
 * @return void
 */
	public function testIsThisMonth() {
		$result = $this->Time->isThisMonth('+0 day');
		$this->assertTrue($result);
		$result = $this->Time->isThisMonth($time = mktime(0, 0, 0, date('m'), mt_rand(1, 28), date('Y')));
		$this->assertTrue($result);
		$result = $this->Time->isThisMonth(mktime(0, 0, 0, date('m'), mt_rand(1, 28), date('Y') - mt_rand(1, 12)));
		$this->assertFalse($result);
		$result = $this->Time->isThisMonth(mktime(0, 0, 0, date('m'), mt_rand(1, 28), date('Y') + mt_rand(1, 12)));
		$this->assertFalse($result);
	}

/**
 * testIsThisYear method
 *
 * @return void
 */
	public function testIsThisYear() {
		$result = $this->Time->isThisYear('+0 day');
		$this->assertTrue($result);
		$result = $this->Time->isThisYear(mktime(0, 0, 0, mt_rand(1, 12), mt_rand(1, 28), date('Y')));
		$this->assertTrue($result);
	}

/**
 * testWasYesterday method
 *
 * @return void
 */
	public function testWasYesterday() {
		$result = $this->Time->wasYesterday('+1 day');
		$this->assertFalse($result);
		$result = $this->Time->wasYesterday('+1 days');
		$this->assertFalse($result);
		$result = $this->Time->wasYesterday('+0 day');
		$this->assertFalse($result);
		$result = $this->Time->wasYesterday('-1 day');
		$this->assertTrue($result);
		$result = $this->Time->wasYesterday('-1 days');
		$this->assertTrue($result);
		$result = $this->Time->wasYesterday('-2 days');
		$this->assertFalse($result);
	}

/**
 * testIsTomorrow method
 *
 * @return void
 */
	public function testIsTomorrow() {
		$result = $this->Time->isTomorrow('+1 day');
		$this->assertTrue($result);
		$result = $this->Time->isTomorrow('+1 days');
		$this->assertTrue($result);
		$result = $this->Time->isTomorrow('+0 day');
		$this->assertFalse($result);
		$result = $this->Time->isTomorrow('-1 day');
		$this->assertFalse($result);
	}

/**
 * testWasWithinLast method
 *
 * @return void
 */
	public function testWasWithinLast() {
		$this->assertTrue($this->Time->wasWithinLast('1 day', '-1 day'));
		$this->assertTrue($this->Time->wasWithinLast('1 week', '-1 week'));
		$this->assertTrue($this->Time->wasWithinLast('1 year', '-1 year'));
		$this->assertTrue($this->Time->wasWithinLast('1 second', '-1 second'));
		$this->assertTrue($this->Time->wasWithinLast('1 minute', '-1 minute'));
		$this->assertTrue($this->Time->wasWithinLast('1 year', '-1 year'));
		$this->assertTrue($this->Time->wasWithinLast('1 month', '-1 month'));
		$this->assertTrue($this->Time->wasWithinLast('1 day', '-1 day'));

		$this->assertTrue($this->Time->wasWithinLast('1 week', '-1 day'));
		$this->assertTrue($this->Time->wasWithinLast('2 week', '-1 week'));
		$this->assertFalse($this->Time->wasWithinLast('1 second', '-1 year'));
		$this->assertTrue($this->Time->wasWithinLast('10 minutes', '-1 second'));
		$this->assertTrue($this->Time->wasWithinLast('23 minutes', '-1 minute'));
		$this->assertFalse($this->Time->wasWithinLast('0 year', '-1 year'));
		$this->assertTrue($this->Time->wasWithinLast('13 month', '-1 month'));
		$this->assertTrue($this->Time->wasWithinLast('2 days', '-1 day'));

		$this->assertFalse($this->Time->wasWithinLast('1 week', '-2 weeks'));
		$this->assertFalse($this->Time->wasWithinLast('1 second', '-2 seconds'));
		$this->assertFalse($this->Time->wasWithinLast('1 day', '-2 days'));
		$this->assertFalse($this->Time->wasWithinLast('1 hour', '-2 hours'));
		$this->assertFalse($this->Time->wasWithinLast('1 month', '-2 months'));
		$this->assertFalse($this->Time->wasWithinLast('1 year', '-2 years'));

		$this->assertFalse($this->Time->wasWithinLast('1 day', '-2 weeks'));
		$this->assertFalse($this->Time->wasWithinLast('1 day', '-2 days'));
		$this->assertFalse($this->Time->wasWithinLast('0 days', '-2 days'));
		$this->assertTrue($this->Time->wasWithinLast('1 hour', '-20 seconds'));
		$this->assertTrue($this->Time->wasWithinLast('1 year', '-60 minutes -30 seconds'));
		$this->assertTrue($this->Time->wasWithinLast('3 years', '-2 months'));
		$this->assertTrue($this->Time->wasWithinLast('5 months', '-4 months'));

		$this->assertTrue($this->Time->wasWithinLast('5 ', '-3 days'));
		$this->assertTrue($this->Time->wasWithinLast('1   ', '-1 hour'));
		$this->assertTrue($this->Time->wasWithinLast('1   ', '-1 minute'));
		$this->assertTrue($this->Time->wasWithinLast('1   ', '-23 hours -59 minutes -59 seconds'));
	}

/**
 * testWasWithinLast method
 *
 * @return void
 */
	public function testIsWithinNext() {
		$this->assertFalse($this->Time->isWithinNext('1 day', '-1 day'));
		$this->assertFalse($this->Time->isWithinNext('1 week', '-1 week'));
		$this->assertFalse($this->Time->isWithinNext('1 year', '-1 year'));
		$this->assertFalse($this->Time->isWithinNext('1 second', '-1 second'));
		$this->assertFalse($this->Time->isWithinNext('1 minute', '-1 minute'));
		$this->assertFalse($this->Time->isWithinNext('1 year', '-1 year'));
		$this->assertFalse($this->Time->isWithinNext('1 month', '-1 month'));
		$this->assertFalse($this->Time->isWithinNext('1 day', '-1 day'));

		$this->assertFalse($this->Time->isWithinNext('1 week', '-1 day'));
		$this->assertFalse($this->Time->isWithinNext('2 week', '-1 week'));
		$this->assertFalse($this->Time->isWithinNext('1 second', '-1 year'));
		$this->assertFalse($this->Time->isWithinNext('10 minutes', '-1 second'));
		$this->assertFalse($this->Time->isWithinNext('23 minutes', '-1 minute'));
		$this->assertFalse($this->Time->isWithinNext('0 year', '-1 year'));
		$this->assertFalse($this->Time->isWithinNext('13 month', '-1 month'));
		$this->assertFalse($this->Time->isWithinNext('2 days', '-1 day'));

		$this->assertFalse($this->Time->isWithinNext('1 week', '-2 weeks'));
		$this->assertFalse($this->Time->isWithinNext('1 second', '-2 seconds'));
		$this->assertFalse($this->Time->isWithinNext('1 day', '-2 days'));
		$this->assertFalse($this->Time->isWithinNext('1 hour', '-2 hours'));
		$this->assertFalse($this->Time->isWithinNext('1 month', '-2 months'));
		$this->assertFalse($this->Time->isWithinNext('1 year', '-2 years'));

		$this->assertFalse($this->Time->isWithinNext('1 day', '-2 weeks'));
		$this->assertFalse($this->Time->isWithinNext('1 day', '-2 days'));
		$this->assertFalse($this->Time->isWithinNext('0 days', '-2 days'));
		$this->assertFalse($this->Time->isWithinNext('1 hour', '-20 seconds'));
		$this->assertFalse($this->Time->isWithinNext('1 year', '-60 minutes -30 seconds'));
		$this->assertFalse($this->Time->isWithinNext('3 years', '-2 months'));
		$this->assertFalse($this->Time->isWithinNext('5 months', '-4 months'));

		$this->assertFalse($this->Time->isWithinNext('5 ', '-3 days'));
		$this->assertFalse($this->Time->isWithinNext('1   ', '-1 hour'));
		$this->assertFalse($this->Time->isWithinNext('1   ', '-1 minute'));
		$this->assertFalse($this->Time->isWithinNext('1   ', '-23 hours -59 minutes -59 seconds'));

		$this->assertTrue($this->Time->isWithinNext('7 days', '6 days, 23 hours, 59 minutes, 59 seconds'));
		$this->assertFalse($this->Time->isWithinNext('7 days', '6 days, 23 hours, 59 minutes, 61 seconds'));
	}

/**
 * testUserOffset method
 *
 * @return void
 */
	public function testUserOffset() {
		$timezoneServer = new DateTimeZone(date_default_timezone_get());
		$timeServer = new DateTime('now', $timezoneServer);
		$yourTimezone = $timezoneServer->getOffset($timeServer) / HOUR;

		$expected = time();
		$result = $this->Time->fromString(time(), $yourTimezone);
		$this->assertWithinMargin($expected, $result, 1);

		$result = $this->Time->fromString(time(), $timezoneServer->getName());
		$this->assertWithinMargin($expected, $result, 1);

		$result = $this->Time->fromString(time(), $timezoneServer);
		$this->assertWithinMargin($expected, $result, 1);

		Configure::write('Config.timezone', $timezoneServer->getName());
		$result = $this->Time->fromString(time());
		$this->assertWithinMargin($expected, $result, 1);
		Configure::delete('Config.timezone');
	}

/**
 * test fromString()
 *
 * @return void
 */
	public function testFromString() {
		$result = $this->Time->fromString('');
		$this->assertFalse($result);

		$result = $this->Time->fromString(0, 0);
		$this->assertFalse($result);

		$result = $this->Time->fromString('+1 hour');
		$expected = strtotime('+1 hour');
		$this->assertWithinMargin($expected, $result, 1);

		$timezone = date('Z', time());
		$result = $this->Time->fromString('+1 hour', $timezone);
		$expected = $this->Time->convert(strtotime('+1 hour'), $timezone);
		$this->assertWithinMargin($expected, $result, 1);

		$timezone = date_default_timezone_get();
		$result = $this->Time->fromString('+1 hour', $timezone);
		$expected = $this->Time->convert(strtotime('+1 hour'), $timezone);
		$this->assertWithinMargin($expected, $result, 1);

		date_default_timezone_set('UTC');
		$date = new DateTime('now', new DateTimeZone('Europe/London'));
		$this->Time->fromString($date);
		$this->assertEquals('Europe/London', $date->getTimeZone()->getName());

		$this->_restoreSystemTimezone();
	}

/**
 * test fromString() with a DateTime object as the dateString
 *
 * @return void
 */
	public function testFromStringWithDateTime() {
		date_default_timezone_set('UTC');
		$date = new DateTime('+1 hour', new DateTimeZone('America/New_York'));
		$result = $this->Time->fromString($date, 'UTC');
		$date->setTimezone(new DateTimeZone('UTC'));
		$expected = $date->format('U') + $date->getOffset();
		$this->assertWithinMargin($expected, $result, 1);
		$this->_restoreSystemTimezone();
	}

	public function testFromStringWithDateTimeAsia() {
		date_default_timezone_set('Australia/Melbourne');
		$date = new DateTime('+1 hour', new DateTimeZone('America/New_York'));
		$result = $this->Time->fromString($date, 'Asia/Kuwait');
		$date->setTimezone(new DateTimeZone('Asia/Kuwait'));
		$expected = $date->format('U') + $date->getOffset();
		$this->assertWithinMargin($expected, $result, 1);
		$this->_restoreSystemTimezone();
	}

	public function testFromStringTimezoneConversionToUTC() {
		date_default_timezone_set('Europe/Copenhagen'); // server timezone
		$clientTimeZone = new DateTimeZone('Asia/Bangkok');
		$clientDateTime = new DateTime('2019-01-31 10:00:00', $clientTimeZone);
		// Convert to UTC.
		$actual = CakeTime::fromString($clientDateTime, 'UTC');
		$clientDateTime->setTimezone(new DateTimeZone('UTC'));
		$expected = $clientDateTime->getTimestamp() + $clientDateTime->getOffset(); // 1548903600
		$this->assertEquals($expected, $actual);
		$this->_restoreSystemTimezone();
	}

	public function testFromStringUTCtoCopenhagen() {
		date_default_timezone_set('UTC'); // server timezone
		$clientTimeZone = new DateTimeZone('UTC');
		$clientDateTime = new DateTime('2012-01-01 10:00:00', $clientTimeZone);
		$actual = CakeTime::fromString($clientDateTime, 'Europe/Copenhagen');
		$clientDateTime->setTimezone(new DateTimeZone('Europe/Copenhagen'));
		$expected = $clientDateTime->getTimestamp() + $clientDateTime->getOffset(); // 1325415600
		$this->assertEquals($expected, $actual);
		$this->_restoreSystemTimezone();
	}

/**
 * Test that datetimes in the default timezone are not modified.
 *
 * @return void
 */
	public function testFromStringWithDateTimeNoConversion() {
		Configure::write('Config.timezone', date_default_timezone_get());
		$date = new DateTime('2013-04-09');
		$result = $this->Time->fromString($date);
		$this->assertEquals($result, $date->format('U'));
	}

	public function testConvertToBangkok() {
		$serverTimeZoneName = 'Europe/Copenhagen';
		date_default_timezone_set($serverTimeZoneName);

		$serverTimeZone = new DateTimeZone($serverTimeZoneName);
		$DateTime = new DateTime('2019-01-31 04:00:00', $serverTimeZone);
		$serverTimestamp = $DateTime->getTimestamp() + $DateTime->getOffset(); // 1548907200

		$clientTimeZoneName = 'Asia/Bangkok';
		$clientTimeZone = new DateTimeZone($clientTimeZoneName);
		$DateTime->setTimezone($clientTimeZone);
		$expected = $DateTime->getTimestamp() + $DateTime->getOffset(); // 1548928800

		$actual = CakeTime::convert($serverTimestamp, $clientTimeZoneName);
		$this->assertEquals($expected, $actual);
		$this->_restoreSystemTimezone();
	}

/**
 * test converting time specifiers using a time definition localfe file
 *
 * @return void
 */
	public function testConvertSpecifiers() {
		App::build(array(
			'Locale' => array(CAKE . 'Test' . DS . 'test_app' . DS . 'Locale' . DS)
		), App::RESET);
		Configure::write('Config.language', 'time_test');
		$time = strtotime('Thu Jan 14 11:43:39 2010');

		$result = $this->Time->convertSpecifiers('%a', $time);
		$expected = 'jue';
		$this->assertEquals($expected, $result);

		$result = $this->Time->convertSpecifiers('%A', $time);
		$expected = 'jueves';
		$this->assertEquals($expected, $result);

		$result = $this->Time->convertSpecifiers('%c', $time);
		$expected = 'jue %d ene %Y %H:%M:%S %Z';
		$this->assertEquals($expected, $result);

		$result = $this->Time->convertSpecifiers('%C', $time);
		$expected = '20';
		$this->assertEquals($expected, $result);

		$result = $this->Time->convertSpecifiers('%D', $time);
		$expected = '%m/%d/%y';
		$this->assertEquals($expected, $result);

		$result = $this->Time->convertSpecifiers('%b', $time);
		$expected = 'ene';
		$this->assertEquals($expected, $result);

		$result = $this->Time->convertSpecifiers('%h', $time);
		$expected = 'ene';
		$this->assertEquals($expected, $result);

		$result = $this->Time->convertSpecifiers('%B', $time);
		$expected = 'enero';
		$this->assertEquals($expected, $result);

		$result = $this->Time->convertSpecifiers('%n', $time);
		$expected = "\n";
		$this->assertEquals($expected, $result);

		$result = $this->Time->convertSpecifiers('%n', $time);
		$expected = "\n";
		$this->assertEquals($expected, $result);

		$result = $this->Time->convertSpecifiers('%p', $time);
		$expected = 'AM';
		$this->assertEquals($expected, $result);

		$result = $this->Time->convertSpecifiers('%P', $time);
		$expected = 'am';
		$this->assertEquals($expected, $result);

		$result = $this->Time->convertSpecifiers('%r', $time);
		$expected = '%I:%M:%S AM';
		$this->assertEquals($expected, $result);

		$result = $this->Time->convertSpecifiers('%R', $time);
		$expected = '11:43';
		$this->assertEquals($expected, $result);

		$result = $this->Time->convertSpecifiers('%t', $time);
		$expected = "\t";
		$this->assertEquals($expected, $result);

		$result = $this->Time->convertSpecifiers('%T', $time);
		$expected = '%H:%M:%S';
		$this->assertEquals($expected, $result);

		$result = $this->Time->convertSpecifiers('%u', $time);
		$expected = 4;
		$this->assertEquals($expected, $result);

		$result = $this->Time->convertSpecifiers('%x', $time);
		$expected = '%d/%m/%y';
		$this->assertEquals($expected, $result);

		$result = $this->Time->convertSpecifiers('%X', $time);
		$expected = '%H:%M:%S';
		$this->assertEquals($expected, $result);
	}

/**
 * test convert %e on Windows.
 *
 * @return void
 */
	public function testConvertPercentE() {
		$this->skipIf(DIRECTORY_SEPARATOR !== '\\', 'Cannot run Windows tests on non-Windows OS.');

		$time = strtotime('Thu Jan 14 11:43:39 2010');
		$result = $this->Time->convertSpecifiers('%e', $time);
		$expected = '14';
		$this->assertEquals($expected, $result);

		$result = $this->Time->convertSpecifiers('%e', strtotime('2011-01-01'));
		$expected = ' 1';
		$this->assertEquals($expected, $result);
	}

/**
 * testListTimezones
 *
 * @return void
 */
	public function testListTimezones() {
		$this->skipIf(
			version_compare(PHP_VERSION, '5.4.0', '<='),
			'This test requires newer libicu which is in php5.4+'
		);
		$return = CakeTime::listTimezones();
		$this->assertTrue(isset($return['Asia']['Asia/Bangkok']));
		$this->assertEquals('Bangkok', $return['Asia']['Asia/Bangkok']);
		$this->assertTrue(isset($return['America']['America/Argentina/Buenos_Aires']));
		$this->assertEquals('Argentina/Buenos_Aires', $return['America']['America/Argentina/Buenos_Aires']);
		$this->assertTrue(isset($return['UTC']['UTC']));
		$this->assertFalse(isset($return['Cuba']));
		$this->assertFalse(isset($return['US']));

		$return = CakeTime::listTimezones('#^Asia/#');
		$this->assertTrue(isset($return['Asia']['Asia/Bangkok']));
		$this->assertFalse(isset($return['Pacific']));

		$return = CakeTime::listTimezones(null, null, array('abbr' => true));
		$this->assertTrue(isset($return['Asia']['Asia/Jakarta']));
		$this->assertEquals('Jakarta - WIB', $return['Asia']['Asia/Jakarta']);
		$this->assertEquals('Regina - CST', $return['America']['America/Regina']);

		$return = CakeTime::listTimezones(null, null, array(
			'abbr' => true,
			'before' => ' (',
			'after' => ')',
		));
		$this->assertEquals('Jayapura (WIT)', $return['Asia']['Asia/Jayapura']);
		$this->assertEquals('Regina (CST)', $return['America']['America/Regina']);

		$return = CakeTime::listTimezones('#^(America|Pacific)/#', null, false);
		$this->assertTrue(isset($return['America/Argentina/Buenos_Aires']));
		$this->assertTrue(isset($return['Pacific/Tahiti']));

		if (!$this->skipIf(version_compare(PHP_VERSION, '5.3.0', '<'))) {
			$return = CakeTime::listTimezones(DateTimeZone::ASIA);
			$this->assertTrue(isset($return['Asia']['Asia/Bangkok']));
			$this->assertFalse(isset($return['Pacific']));

			$return = CakeTime::listTimezones(DateTimeZone::PER_COUNTRY, 'US', false);
			$this->assertTrue(isset($return['Pacific/Honolulu']));
			$this->assertFalse(isset($return['Asia/Bangkok']));
		}
	}

}
