<?php
/**
 * CakeTime utility class file.
 *
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @package       Cake.Utility
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Multibyte', 'I18n');

/**
 * Time Helper class for easy use of time data.
 *
 * Manipulation of time data.
 *
 * @package       Cake.Utility
 * @link https://book.cakephp.org/2.0/en/core-libraries/helpers/time.html
 */
class CakeTime {

 /**
 * Temporary variable containing the timestamp value, used internally in convertSpecifiers()
 *
 * @var int
 */
	protected static $_time = null;

/**
 * Magic set method for backwards compatibility.
 * Used by TimeHelper to modify static variables in CakeTime
 *
 * @param string $name Variable name
 * @param mixes $value Variable value
 * @return void
 */
	public function __set($name, $value) {

	}

/**
 * Magic set method for backwards compatibility.
 * Used by TimeHelper to get static variables in CakeTime
 *
 * @param string $name Variable name
 * @return mixed
 */
	public function __get($name) {
		return null;
	}

/**
 * Converts a string representing the format for the function strftime and returns a
 * Windows safe and i18n aware format.
 *
 * @param string $format Format with specifiers for strftime function.
 *    Accepts the special specifier %S which mimics the modifier S for date()
 * @param string $time UNIX timestamp
 * @return string Windows safe and date() function compatible format for strftime
 * @link https://book.cakephp.org/2.0/en/core-libraries/helpers/time.html#TimeHelper::convertSpecifiers
 */
	public static function convertSpecifiers($format, $time = null) {
		if (!$time) {
			$time = time();
		}
		static::$_time = $time;
		return preg_replace_callback('/\%(\w+)/', array('CakeTime', '_translateSpecifier'), $format);
	}

/**
 * Auxiliary function to translate a matched specifier element from a regular expression into
 * a Windows safe and i18n aware specifier
 *
 * @param array $specifier match from regular expression
 * @return string converted element
 */
	protected static function _translateSpecifier($specifier) {
		switch ($specifier[1]) {
			case 'a':
				$abday = __dc('cake', 'abday', 5);
				if (is_array($abday)) {
					return $abday[date('w', static::$_time)];
				}
				break;
			case 'A':
				$day = __dc('cake', 'day', 5);
				if (is_array($day)) {
					return $day[date('w', static::$_time)];
				}
				break;
			case 'c':
				$format = __dc('cake', 'd_t_fmt', 5);
				if ($format !== 'd_t_fmt') {
					return static::convertSpecifiers($format, static::$_time);
				}
				break;
			case 'C':
				return sprintf("%02d", date('Y', static::$_time) / 100);
			case 'D':
				return '%m/%d/%y';
			case 'e':
				if (DS === '/') {
					return '%e';
				}
				$day = date('j', static::$_time);
				if ($day < 10) {
					$day = ' ' . $day;
				}
				return $day;
			case 'eS' :
				return date('jS', static::$_time);
			case 'b':
			case 'h':
				$months = __dc('cake', 'abmon', 5);
				if (is_array($months)) {
					return $months[date('n', static::$_time) - 1];
				}
				return '%b';
			case 'B':
				$months = __dc('cake', 'mon', 5);
				if (is_array($months)) {
					return $months[date('n', static::$_time) - 1];
				}
				break;
			case 'n':
				return "\n";
			case 'p':
			case 'P':
				$default = array('am' => 0, 'pm' => 1);
				$meridiem = $default[date('a', static::$_time)];
				$format = __dc('cake', 'am_pm', 5);
				if (is_array($format)) {
					$meridiem = $format[$meridiem];
					return ($specifier[1] === 'P') ? strtolower($meridiem) : strtoupper($meridiem);
				}
				break;
			case 'r':
				$complete = __dc('cake', 't_fmt_ampm', 5);
				if ($complete !== 't_fmt_ampm') {
					return str_replace('%p', static::_translateSpecifier(array('%p', 'p')), $complete);
				}
				break;
			case 'R':
				return date('H:i', static::$_time);
			case 't':
				return "\t";
			case 'T':
				return '%H:%M:%S';
			case 'u':
				return ($weekDay = date('w', static::$_time)) ? $weekDay : 7;
			case 'x':
				$format = __dc('cake', 'd_fmt', 5);
				if ($format !== 'd_fmt') {
					return static::convertSpecifiers($format, static::$_time);
				}
				break;
			case 'X':
				$format = __dc('cake', 't_fmt', 5);
				if ($format !== 't_fmt') {
					return static::convertSpecifiers($format, static::$_time);
				}
				break;
		}
		return $specifier[0];
	}

/**
 * Converts given time (in server's time zone) to user's local time, given his/her timezone.
 *
 * @param int $serverTime Server's timestamp.
 * @param string|DateTimeZone $timezone User's timezone string or DateTimeZone object.
 * @return int User's timezone timestamp.
 * @link https://book.cakephp.org/2.0/en/core-libraries/helpers/time.html#TimeHelper::convert
 */
	public static function convert($serverTime, $timezone) {
		static $serverTimezone = null;
		if ($serverTimezone === null || (date_default_timezone_get() !== $serverTimezone->getName())) {
			$serverTimezone = new DateTimeZone(date_default_timezone_get());
		}
		$serverOffset = $serverTimezone->getOffset(new DateTime('@' . $serverTime));
		$gmtTime = $serverTime - $serverOffset;
		if (is_numeric($timezone)) {
			$userOffset = $timezone * (60 * 60);
		} else {
			$timezone = static::timezone($timezone);
			$userOffset = $timezone->getOffset(new DateTime('@' . $gmtTime));
		}
		$userTime = $gmtTime + $userOffset;
		return (int)$userTime;
	}

/**
 * Returns a timezone object from a string or the user's timezone object
 *
 * @param string|DateTimeZone $timezone Timezone string or DateTimeZone object
 * 	If null it tries to get timezone from 'Config.timezone' config var
 * @return DateTimeZone Timezone object
 * @link https://book.cakephp.org/2.0/en/core-libraries/helpers/time.html#TimeHelper::timezone
 */
	public static function timezone($timezone = null) {
		static $tz = null;

		if (is_object($timezone)) {
			if ($tz === null || $tz->getName() !== $timezone->getName()) {
				$tz = $timezone;
			}
		} else {
			if ($timezone === null) {
				$timezone = Configure::read('Config.timezone');
				if ($timezone === null) {
					$timezone = date_default_timezone_get();
				}
			}

			if ($tz === null || $tz->getName() !== $timezone) {
				$tz = new DateTimeZone($timezone);
			}
		}

		return $tz;
	}

/**
 * Returns server's offset from GMT in seconds.
 *
 * @return int Offset
 * @link https://book.cakephp.org/2.0/en/core-libraries/helpers/time.html#TimeHelper::serverOffset
 */
	public static function serverOffset() {
		return date('Z', time());
	}

/**
 * Returns a timestamp, given either a UNIX timestamp or a valid strtotime() date string.
 *
 * @param int|string|DateTime $dateString UNIX timestamp, strtotime() valid string or DateTime object
 * @param string|DateTimeZone $timezone Timezone string or DateTimeZone object
 * @return int|false Parsed given timezone timestamp.
 * @link https://book.cakephp.org/2.0/en/core-libraries/helpers/time.html#TimeHelper::fromString
 */
	public static function fromString($dateString, $timezone = null) {
		if (empty($dateString)) {
			return false;
		}

		$containsDummyDate = (is_string($dateString) && substr($dateString, 0, 10) === '0000-00-00');
		if ($containsDummyDate) {
			return false;
		}

		if (is_int($dateString) || is_numeric($dateString)) {
			$date = (int)$dateString;
		} elseif ($dateString instanceof DateTime &&
			$dateString->getTimezone()->getName() != date_default_timezone_get()
		) {
			$clone = clone $dateString;
			$clone->setTimezone(new DateTimeZone(date_default_timezone_get()));
			$date = (int)$clone->format('U') + $clone->getOffset();
		} elseif ($dateString instanceof DateTime) {
			$date = (int)$dateString->format('U');
		} else {
			$date = strtotime($dateString);
		}

		if ($date === -1 || empty($date)) {
			return false;
		}

		if ($timezone === null) {
			$timezone = Configure::read('Config.timezone');
		}

		if ($timezone !== null) {
			return static::convert($date, $timezone);
		}
		return $date;
	}

/**
 * Returns a partial SQL string to search for all records between two dates.
 *
 * @param int|string|DateTime $begin UNIX timestamp, strtotime() valid string or DateTime object
 * @param int|string|DateTime $end UNIX timestamp, strtotime() valid string or DateTime object
 * @param string $fieldName Name of database field to compare with
 * @param string|DateTimeZone $timezone Timezone string or DateTimeZone object
 * @return string Partial SQL string.
 * @link https://book.cakephp.org/2.0/en/core-libraries/helpers/time.html#TimeHelper::daysAsSql
 */
	public static function daysAsSql($begin, $end, $fieldName, $timezone = null) {
		$begin = static::fromString($begin, $timezone);
		$end = static::fromString($end, $timezone);
		$begin = date('Y-m-d', $begin) . ' 00:00:00';
		$end = date('Y-m-d', $end) . ' 23:59:59';

		return "($fieldName >= '$begin') AND ($fieldName <= '$end')";
	}

/**
 * Returns a partial SQL string to search for all records between two times
 * occurring on the same day.
 *
 * @param int|string|DateTime $dateString UNIX timestamp, strtotime() valid string or DateTime object
 * @param string $fieldName Name of database field to compare with
 * @param string|DateTimeZone $timezone Timezone string or DateTimeZone object
 * @return string Partial SQL string.
 * @link https://book.cakephp.org/2.0/en/core-libraries/helpers/time.html#TimeHelper::dayAsSql
 */
	public static function dayAsSql($dateString, $fieldName, $timezone = null) {
		return static::daysAsSql($dateString, $dateString, $fieldName, $timezone);
	}

/**
 * Returns true if given datetime string is today.
 *
 * @param int|string|DateTime $dateString UNIX timestamp, strtotime() valid string or DateTime object
 * @param string|DateTimeZone $timezone Timezone string or DateTimeZone object
 * @return bool True if datetime string is today
 * @link https://book.cakephp.org/2.0/en/core-libraries/helpers/time.html#TimeHelper::isToday
 */
	public static function isToday($dateString, $timezone = null) {
		$timestamp = static::fromString($dateString, $timezone);
		$now = static::fromString('now', $timezone);
		return date('Y-m-d', $timestamp) === date('Y-m-d', $now);
	}

/**
 * Returns true if given datetime string is in the future.
 *
 * @param int|string|DateTime $dateString UNIX timestamp, strtotime() valid string or DateTime object
 * @param string|DateTimeZone $timezone Timezone string or DateTimeZone object
 * @return bool True if datetime string is in the future
 * @link https://book.cakephp.org/2.0/en/core-libraries/helpers/time.html#TimeHelper::isFuture
 */
	public static function isFuture($dateString, $timezone = null) {
		$timestamp = static::fromString($dateString, $timezone);
		return $timestamp > time();
	}

/**
 * Returns true if given datetime string is in the past.
 *
 * @param int|string|DateTime $dateString UNIX timestamp, strtotime() valid string or DateTime object
 * @param string|DateTimeZone $timezone Timezone string or DateTimeZone object
 * @return bool True if datetime string is in the past
 * @link https://book.cakephp.org/2.0/en/core-libraries/helpers/time.html#TimeHelper::isPast
 */
	public static function isPast($dateString, $timezone = null) {
		$timestamp = static::fromString($dateString, $timezone);
		return $timestamp < time();
	}

/**
 * Returns true if given datetime string is within this week.
 *
 * @param int|string|DateTime $dateString UNIX timestamp, strtotime() valid string or DateTime object
 * @param string|DateTimeZone $timezone Timezone string or DateTimeZone object
 * @return bool True if datetime string is within current week
 * @link https://book.cakephp.org/2.0/en/core-libraries/helpers/time.html#TimeHelper::isThisWeek
 */
	public static function isThisWeek($dateString, $timezone = null) {
		$timestamp = static::fromString($dateString, $timezone);
		$now = static::fromString('now', $timezone);
		return date('W o', $timestamp) === date('W o', $now);
	}

/**
 * Returns true if given datetime string is within this month
 *
 * @param int|string|DateTime $dateString UNIX timestamp, strtotime() valid string or DateTime object
 * @param string|DateTimeZone $timezone Timezone string or DateTimeZone object
 * @return bool True if datetime string is within current month
 * @link https://book.cakephp.org/2.0/en/core-libraries/helpers/time.html#TimeHelper::isThisMonth
 */
	public static function isThisMonth($dateString, $timezone = null) {
		$timestamp = static::fromString($dateString, $timezone);
		$now = static::fromString('now', $timezone);
		return date('m Y', $timestamp) === date('m Y', $now);
	}

/**
 * Returns true if given datetime string is within current year.
 *
 * @param int|string|DateTime $dateString UNIX timestamp, strtotime() valid string or DateTime object
 * @param string|DateTimeZone $timezone Timezone string or DateTimeZone object
 * @return bool True if datetime string is within current year
 * @link https://book.cakephp.org/2.0/en/core-libraries/helpers/time.html#TimeHelper::isThisYear
 */
	public static function isThisYear($dateString, $timezone = null) {
		$timestamp = static::fromString($dateString, $timezone);
		$now = static::fromString('now', $timezone);
		return date('Y', $timestamp) === date('Y', $now);
	}

/**
 * Returns true if given datetime string was yesterday.
 *
 * @param int|string|DateTime $dateString UNIX timestamp, strtotime() valid string or DateTime object
 * @param string|DateTimeZone $timezone Timezone string or DateTimeZone object
 * @return bool True if datetime string was yesterday
 * @link https://book.cakephp.org/2.0/en/core-libraries/helpers/time.html#TimeHelper::wasYesterday
 */
	public static function wasYesterday($dateString, $timezone = null) {
		$timestamp = static::fromString($dateString, $timezone);
		$yesterday = static::fromString('yesterday', $timezone);
		return date('Y-m-d', $timestamp) === date('Y-m-d', $yesterday);
	}

/**
 * Returns true if given datetime string is tomorrow.
 *
 * @param int|string|DateTime $dateString UNIX timestamp, strtotime() valid string or DateTime object
 * @param string|DateTimeZone $timezone Timezone string or DateTimeZone object
 * @return bool True if datetime string was yesterday
 * @link https://book.cakephp.org/2.0/en/core-libraries/helpers/time.html#TimeHelper::isTomorrow
 */
	public static function isTomorrow($dateString, $timezone = null) {
		$timestamp = static::fromString($dateString, $timezone);
		$tomorrow = static::fromString('tomorrow', $timezone);
		return date('Y-m-d', $timestamp) === date('Y-m-d', $tomorrow);
	}

/**
 * Returns the quarter
 *
 * @param int|string|DateTime $dateString UNIX timestamp, strtotime() valid string or DateTime object
 * @param bool $range if true returns a range in Y-m-d format
 * @return int|array 1, 2, 3, or 4 quarter of year or array if $range true
 * @link https://book.cakephp.org/2.0/en/core-libraries/helpers/time.html#TimeHelper::toQuarter
 */
	public static function toQuarter($dateString, $range = false) {
		$time = static::fromString($dateString);
		$date = (int)ceil(date('m', $time) / 3);
		if ($range === false) {
			return $date;
		}

		$year = date('Y', $time);
		switch ($date) {
			case 1:
				return array($year . '-01-01', $year . '-03-31');
			case 2:
				return array($year . '-04-01', $year . '-06-30');
			case 3:
				return array($year . '-07-01', $year . '-09-30');
			case 4:
				return array($year . '-10-01', $year . '-12-31');
		}
	}

/**
 * Returns a UNIX timestamp from a textual datetime description. Wrapper for PHP function strtotime().
 *
 * @param int|string|DateTime $dateString UNIX timestamp, strtotime() valid string or DateTime object
 * @param string|DateTimeZone $timezone Timezone string or DateTimeZone object
 * @return int Unix timestamp
 * @link https://book.cakephp.org/2.0/en/core-libraries/helpers/time.html#TimeHelper::toUnix
 */
	public static function toUnix($dateString, $timezone = null) {
		return static::fromString($dateString, $timezone);
	}

/**
 * Returns a formatted date in server's timezone.
 *
 * If a DateTime object is given or the dateString has a timezone
 * segment, the timezone parameter will be ignored.
 *
 * If no timezone parameter is given and no DateTime object, the passed $dateString will be
 * considered to be in the UTC timezone.
 *
 * @param int|string|DateTime $dateString UNIX timestamp, strtotime() valid string or DateTime object
 * @param string|DateTimeZone $timezone Timezone string or DateTimeZone object
 * @param string $format date format string
 * @return mixed Formatted date
 * @link https://book.cakephp.org/2.0/en/core-libraries/helpers/time.html#TimeHelper::toServer
 */
	public static function toServer($dateString, $timezone = null, $format = 'Y-m-d H:i:s') {
		if ($timezone === null) {
			$timezone = new DateTimeZone('UTC');
		} elseif (is_string($timezone)) {
			$timezone = new DateTimeZone($timezone);
		} elseif (!($timezone instanceof DateTimeZone)) {
			return false;
		}

		if ($dateString instanceof DateTime) {
			$date = $dateString;
		} elseif (is_int($dateString) || is_numeric($dateString)) {
			$dateString = (int)$dateString;

			$date = new DateTime('@' . $dateString);
			$date->setTimezone($timezone);
		} else {
			$date = new DateTime($dateString, $timezone);
		}

		$date->setTimezone(new DateTimeZone(date_default_timezone_get()));
		return $date->format($format);
	}

/**
 * Returns a date formatted for Atom RSS feeds.
 *
 * @param string $dateString Datetime string or Unix timestamp
 * @param string|DateTimeZone $timezone Timezone string or DateTimeZone object
 * @return string Formatted date string
 * @link https://book.cakephp.org/2.0/en/core-libraries/helpers/time.html#TimeHelper::toAtom
 */
	public static function toAtom($dateString, $timezone = null) {
		return date('Y-m-d\TH:i:s\Z', static::fromString($dateString, $timezone));
	}

/**
 * Formats date for RSS feeds
 *
 * @param int|string|DateTime $dateString UNIX timestamp, strtotime() valid string or DateTime object
 * @param string|DateTimeZone $timezone Timezone string or DateTimeZone object
 * @return string Formatted date string
 * @link https://book.cakephp.org/2.0/en/core-libraries/helpers/time.html#TimeHelper::toRSS
 */
	public static function toRSS($dateString, $timezone = null) {
		$date = static::fromString($dateString, $timezone);

		if ($timezone === null) {
			return date("r", $date);
		}

		$userOffset = $timezone;
		if (!is_numeric($timezone)) {
			if (!is_object($timezone)) {
				$timezone = new DateTimeZone($timezone);
			}
			$currentDate = new DateTime('@' . $date);
			$currentDate->setTimezone($timezone);
			$userOffset = $timezone->getOffset($currentDate) / 60 / 60;
		}

		$timezone = '+0000';
		if ($userOffset != 0) {
			$hours = (int)floor(abs($userOffset));
			$minutes = (int)(fmod(abs($userOffset), $hours) * 60);
			$timezone = ($userOffset < 0 ? '-' : '+') . str_pad($hours, 2, '0', STR_PAD_LEFT) . str_pad($minutes, 2, '0', STR_PAD_LEFT);
		}
		return date('D, d M Y H:i:s', $date) . ' ' . $timezone;
	}

/**
 * Returns true if specified datetime was within the interval specified, else false.
 *
 * @param string|int $timeInterval the numeric value with space then time type.
 *    Example of valid types: 6 hours, 2 days, 1 minute.
 * @param int|string|DateTime $dateString UNIX timestamp, strtotime() valid string or DateTime object
 * @param string|DateTimeZone $timezone Timezone string or DateTimeZone object
 * @return bool
 * @link https://book.cakephp.org/2.0/en/core-libraries/helpers/time.html#TimeHelper::wasWithinLast
 */
	public static function wasWithinLast($timeInterval, $dateString, $timezone = null) {
		$tmp = str_replace(' ', '', $timeInterval);
		if (is_numeric($tmp)) {
			$timeInterval = $tmp . ' ' . __d('cake', 'days');
		}

		$date = static::fromString($dateString, $timezone);
		$interval = static::fromString('-' . $timeInterval);
		$now = static::fromString('now', $timezone);

		return $date >= $interval && $date <= $now;
	}

/**
 * Returns true if specified datetime is within the interval specified, else false.
 *
 * @param string|int $timeInterval the numeric value with space then time type.
 *    Example of valid types: 6 hours, 2 days, 1 minute.
 * @param int|string|DateTime $dateString UNIX timestamp, strtotime() valid string or DateTime object
 * @param string|DateTimeZone $timezone Timezone string or DateTimeZone object
 * @return bool
 */
	public static function isWithinNext($timeInterval, $dateString, $timezone = null) {
		$tmp = str_replace(' ', '', $timeInterval);
		if (is_numeric($tmp)) {
			$timeInterval = $tmp . ' ' . __d('cake', 'days');
		}

		$date = static::fromString($dateString, $timezone);
		$interval = static::fromString('+' . $timeInterval);
		$now = static::fromString('now', $timezone);

		return $date <= $interval && $date >= $now;
	}

/**
 * Returns gmt as a UNIX timestamp.
 *
 * @param int|string|DateTime $dateString UNIX timestamp, strtotime() valid string or DateTime object
 * @return int UNIX timestamp
 * @link https://book.cakephp.org/2.0/en/core-libraries/helpers/time.html#TimeHelper::gmt
 */
	public static function gmt($dateString = null) {
		$time = time();
		if ($dateString) {
			$time = static::fromString($dateString);
		}
		return gmmktime(
			(int)date('G', $time),
			(int)date('i', $time),
			(int)date('s', $time),
			(int)date('n', $time),
			(int)date('j', $time),
			(int)date('Y', $time)
		);
	}

/**
 * Get list of timezone identifiers
 *
 * @param int|string $filter A regex to filter identifier
 * 	Or one of DateTimeZone class constants (PHP 5.3 and above)
 * @param string $country A two-letter ISO 3166-1 compatible country code.
 * 	This option is only used when $filter is set to DateTimeZone::PER_COUNTRY (available only in PHP 5.3 and above)
 * @param bool|array $options If true (default value) groups the identifiers list by primary region.
 * 	Otherwise, an array containing `group`, `abbr`, `before`, and `after` keys.
 * 	Setting `group` and `abbr` to true will group results and append timezone
 * 	abbreviation in the display value. Set `before` and `after` to customize
 * 	the abbreviation wrapper.
 * @return array List of timezone identifiers
 * @since 2.2
 * @link https://book.cakephp.org/2.0/en/core-libraries/helpers/time.html#TimeHelper::listTimezones
 */
	public static function listTimezones($filter = null, $country = null, $options = array()) {
		if (is_bool($options)) {
			$options = array(
				'group' => $options,
			);
		}
		$defaults = array(
			'group' => true,
			'abbr' => false,
			'before' => ' - ',
			'after' => null,
		);
		$options += $defaults;
		$group = $options['group'];

		$regex = null;
		if (is_string($filter)) {
			$regex = $filter;
			$filter = null;
		}
		if (version_compare(PHP_VERSION, '5.3.0', '<')) {
			if ($regex === null) {
				$regex = '#^((Africa|America|Antartica|Arctic|Asia|Atlantic|Australia|Europe|Indian|Pacific)/|UTC)#';
			}
			$identifiers = DateTimeZone::listIdentifiers();
		} else {
			if ($filter === null) {
				$filter = DateTimeZone::ALL;
			}
			$identifiers = DateTimeZone::listIdentifiers($filter, $country);
		}

		if ($regex) {
			foreach ($identifiers as $key => $tz) {
				if (!preg_match($regex, $tz)) {
					unset($identifiers[$key]);
				}
			}
		}

		if ($group) {
			$return = array();
			$now = time();
			$before = $options['before'];
			$after = $options['after'];
			foreach ($identifiers as $key => $tz) {
				$abbr = null;
				if ($options['abbr']) {
					$dateTimeZone = new DateTimeZone($tz);
					$trans = $dateTimeZone->getTransitions($now, $now);
					$abbr = isset($trans[0]['abbr']) ?
						$before . $trans[0]['abbr'] . $after :
						null;
				}
				$item = explode('/', $tz, 2);
				if (isset($item[1])) {
					$return[$item[0]][$tz] = $item[1] . $abbr;
				} else {
					$return[$item[0]] = array($tz => $item[0] . $abbr);
				}
			}
			return $return;
		}
		return array_combine($identifiers, $identifiers);
	}

}
