<?php
/**
 * ConsoleOutputTest file
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
 * @package       Cake.Test.Case.Console
 * @since         CakePHP(tm) v 1.2.0.5432
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

App::uses('ConsoleOutput', 'Console');

/**
 * ConsoleOutputTest
 *
 *
 * @package       Cake.Test.Case.Console
 */
class ConsoleOutputTest extends CakeTestCase {

    /** @var ConsoleOutput|\PHPUnit\Framework\MockObject\MockObject  */
    protected $out;

    /**
 * setup
 *
 * @return void
 */
	public function setUp(): void {
		parent::setUp();
		$this->out = $this->getMock('ConsoleOutput', array('_write'));
		$this->out->outputAs(ConsoleOutput::COLOR);
	}

/**
 * tearDown
 *
 * @return void
 */
	public function tearDown(): void {
		parent::tearDown();
		unset($this->out);
	}

/**
 * test writing with no new line
 *
 * @return void
 */
	public function testWriteNoNewLine() {
		$this->out->expects($this->once())->method('_write')
			->with('Some output');

		$this->out->write('Some output', false);
	}

/**
 * test writing with no new line
 *
 * @return void
 */
	public function testWriteNewLine() {
		$this->out->expects($this->once())->method('_write')
			->with('Some output' . PHP_EOL);

		$this->out->write('Some output');
	}

/**
 * test write() with multiple new lines
 *
 * @return void
 */
	public function testWriteMultipleNewLines() {
		$this->out->expects($this->once())->method('_write')
			->with('Some output' . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL);

		$this->out->write('Some output', 4);
	}

/**
 * test writing an array of messages.
 *
 * @return void
 */
	public function testWriteArray() {
		$this->out->expects($this->once())->method('_write')
			->with('Line' . PHP_EOL . 'Line' . PHP_EOL . 'Line' . PHP_EOL);

		$this->out->write(array('Line', 'Line', 'Line'));
	}

/**
 * test writing an array of messages.
 *
 * @return void
 */
	public function testOverwrite() {
		$testString = "Text";

		$this->out->expects($this->at(0))->method('_write')
			->with($testString);

		$this->out->expects($this->at(1))->method('_write')
			->with("");

		$this->out->expects($this->at(2))->method('_write')
			->with("Overwriting text");

		$this->out->write($testString, 0);
		$this->out->overwrite("Overwriting text");
	}

/**
 * test getting a style.
 *
 * @return void
 */
	public function testStylesGet() {
		$result = $this->out->styles('error');
		$expected = array('text' => 'red', 'underline' => true);
		$this->assertEquals($expected, $result);

		$this->assertNull($this->out->styles('made_up_goop'));

		$result = $this->out->styles();
		$this->assertNotEmpty($result, 'error', 'Error is missing');
		$this->assertNotEmpty($result, 'warning', 'Warning is missing');
	}

/**
 * test adding a style.
 *
 * @return void
 */
	public function testStylesAdding() {
		$this->out->styles('test', array('text' => 'red', 'background' => 'black'));
		$result = $this->out->styles('test');
		$expected = array('text' => 'red', 'background' => 'black');
		$this->assertEquals($expected, $result);

		$this->assertTrue($this->out->styles('test', false), 'Removing a style should return true.');
		$this->assertNull($this->out->styles('test'), 'Removed styles should be null.');
	}

/**
 * test formatting text with styles.
 *
 * @return void
 */
	public function testFormattingSimple() {
		$this->out->expects($this->once())->method('_write')
			->with("\033[31;4mError:\033[0m Something bad");

		$this->out->write('<error>Error:</error> Something bad', false);
	}

/**
 * test that formatting doesn't eat tags it doesn't know about.
 *
 * @return void
 */
	public function testFormattingNotEatingTags() {
		$this->out->expects($this->once())->method('_write')
			->with("<red> Something bad");

		$this->out->write('<red> Something bad', false);
	}

/**
 * test formatting with custom styles.
 *
 * @return void
 */
	public function testFormattingCustom() {
		$this->out->styles('annoying', array(
			'text' => 'magenta',
			'background' => 'cyan',
			'blink' => true,
			'underline' => true
		));

		$this->out->expects($this->once())->method('_write')
			->with("\033[35;46;5;4mAnnoy:\033[0m Something bad");

		$this->out->write('<annoying>Annoy:</annoying> Something bad', false);
	}

/**
 * test formatting text with missing styles.
 *
 * @return void
 */
	public function testFormattingMissingStyleName() {
		$this->out->expects($this->once())->method('_write')
			->with("<not_there>Error:</not_there> Something bad");

		$this->out->write('<not_there>Error:</not_there> Something bad', false);
	}

/**
 * test formatting text with multiple styles.
 *
 * @return void
 */
	public function testFormattingMultipleStylesName() {
		$this->out->expects($this->once())->method('_write')
			->with("\033[31;4mBad\033[0m \033[33mWarning\033[0m Regular");

		$this->out->write('<error>Bad</error> <warning>Warning</warning> Regular', false);
	}

/**
 * test that multiple tags of the same name work in one string.
 *
 * @return void
 */
	public function testFormattingMultipleSameTags() {
		$this->out->expects($this->once())->method('_write')
			->with("\033[31;4mBad\033[0m \033[31;4mWarning\033[0m Regular");

		$this->out->write('<error>Bad</error> <error>Warning</error> Regular', false);
	}

/**
 * test raw output not getting tags replaced.
 *
 * @return void
 */
	public function testOutputAsRaw() {
		$this->out->outputAs(ConsoleOutput::RAW);
		$this->out->expects($this->once())->method('_write')
			->with('<error>Bad</error> Regular');

		$this->out->write('<error>Bad</error> Regular', false);
	}

/**
 * test plain output.
 *
 * @return void
 */
	public function testOutputAsPlain() {
		$this->out->outputAs(ConsoleOutput::PLAIN);
		$this->out->expects($this->once())->method('_write')
			->with('Bad Regular');

		$this->out->write('<error>Bad</error> Regular', false);
	}

/**
 * test plain output when php://output, as php://output is
 * not compatible with posix_ functions.
 *
 * @return void
 */
	public function testOutputAsPlainWhenOutputStream() {
		$output = $this->getMock('ConsoleOutput', array('_write'), array('php://output'));
		$this->assertEquals(ConsoleOutput::PLAIN, $output->outputAs());
	}

/**
 * test plain output only strips tags used for formatting.
 *
 * @return void
 */
	public function testOutputAsPlainSelectiveTagRemoval() {
		$this->out->outputAs(ConsoleOutput::PLAIN);
		$this->out->expects($this->once())->method('_write')
			->with('Bad Regular <b>Left</b> <i>behind</i> <name>');

		$this->out->write('<error>Bad</error> Regular <b>Left</b> <i>behind</i> <name>', false);
	}
}
