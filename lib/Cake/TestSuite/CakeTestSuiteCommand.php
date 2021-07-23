<?php
/**
 * TestRunner for CakePHP Test suite.
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
 * @package       Cake.TestSuite
 * @since         CakePHP(tm) v 2.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

use PHPUnit\TextUI\Command;

App::uses('CakeTestRunner', 'TestSuite');
App::uses('CakeTestLoader', 'TestSuite');
App::uses('CakeTestSuite', 'TestSuite');
App::uses('CakeTestCase', 'TestSuite');
App::uses('ControllerTestCase', 'TestSuite');
App::uses('CakeTestModel', 'TestSuite/Fixture');

/**
 * Class to customize loading of test suites from CLI
 *
 * @property array $_params
 * @package       Cake.TestSuite
 */
class CakeTestSuiteCommand extends Command {

/**
 * Construct method
 *
 * @param mixed $loader The loader instance to use.
 * @param array $params list of options to be used for this run
 * @throws MissingTestLoaderException When a loader class could not be found.
 */
	public function __construct($loader, $params = array()) {
		if ($loader && !class_exists($loader)) {
			throw new MissingTestLoaderException(array('class' => $loader));
		}
		$this->arguments['loader'] = $loader;
		$this->arguments['test'] = $params['case'];
		$this->arguments['testFile'] = $params;
		$this->_params = $params;

		$this->longOptions['fixture='] = 'handleFixture';
		$this->longOptions['output='] = 'handleReporter';
	}

    /**
     * Convert path fragments used by CakePHP's test runner to absolute paths that can be fed to PHPUnit.
     *
     * @param string $filePath The file path to load.
     * @param string $params Additional parameters.
     * @return string Converted path fragments.
     */
    protected function _resolveTestFile($filePath, $params) {
        $basePath = $this->_basePath($params) . DS . $filePath;
        $ending = 'Test.php';
        return (strpos($basePath, $ending) === (strlen($basePath) - strlen($ending))) ? $basePath : $basePath . $ending;
    }

    /**
     * Generates the base path to a set of tests based on the parameters.
     *
     * @param array $params The path parameters.
     * @return string The base path.
     */
    protected static function _basePath($params) {
        $result = null;
        if (!empty($params['core'])) {
            $result = CORE_TEST_CASES;
        } elseif (!empty($params['plugin'])) {
            if (!CakePlugin::loaded($params['plugin'])) {
                try {
                    CakePlugin::load($params['plugin']);
                    $result = CakePlugin::path($params['plugin']) . 'Test' . DS . 'Case';
                } catch (MissingPluginException $e) {
                }
            } else {
                $result = CakePlugin::path($params['plugin']) . 'Test' . DS . 'Case';
            }
        } elseif (!empty($params['app'])) {
            $result = APP_TEST_CASES;
        }
        return $result;
    }


/**
 * Ugly hack to get around PHPUnit having a hard coded class name for the Runner. :(
 *
 * @param array $argv The command arguments
 * @param bool $exit The exit mode.
 * @return int
 */
	public function run(array $argv, bool $exit = true): int
    {
		$this->handleArguments($argv);

		$runner = $this->getRunner($this->arguments['loader']);

		if (is_object($this->arguments['test']) &&
			$this->arguments['test'] instanceof PHPUnit\Framework\Test) {
			$suite = $this->arguments['test'];
		} else {
		    $testFile = $this->_resolveTestFile($this->arguments['test'], $this->arguments['testFile']);
			$suite = $runner->getTest(
				$this->arguments['test'],
                $testFile
			);
		}

		if ($this->arguments['listGroups']) {

			print "Available test group(s):\n";

			$groups = $suite->getGroups();
			sort($groups);

			foreach ($groups as $group) {
				print " - $group\n";
			}

			if($exit) {
                exit(PHPUnit\TextUI\TestRunner::SUCCESS_EXIT);
            }
			return PHPUnit\TextUI\TestRunner::SUCCESS_EXIT;
		}

		unset($this->arguments['test']);
		unset($this->arguments['testFile']);

		try {
			$result = $runner->doRun($suite, $this->arguments, false);
		} catch (PHPUnit\Framework\Exception $e) {
			print $e->getMessage() . "\n";
		}

        if (!isset($result) || $result->errorCount() > 0) {
            $return = PHPUnit\TextUI\TestRunner::EXCEPTION_EXIT;
        } elseif ($result->failureCount() > 0) {
            $return = PHPUnit\TextUI\TestRunner::FAILURE_EXIT;
        }
        else {
            // Default to success even if there are warnings to match phpunit's behavior
            $return = PHPUnit\TextUI\TestRunner::SUCCESS_EXIT;
        }

        if ($exit) {
            exit($return);
		}
        return $return;
	}

/**
 * Create a runner for the command.
 *
 * @param mixed $loader The loader to be used for the test run.
 * @return CakeTestRunner
 */
	public function getRunner($loader) {
		return new CakeTestRunner($loader, $this->_params);
	}

/**
 * Handler for customizing the FixtureManager class/
 *
 * @param string $class Name of the class that will be the fixture manager
 * @return void
 */
	public function handleFixture($class) {
		$this->arguments['fixtureManager'] = $class;
	}

/**
 * Handles output flag used to change printing on webrunner.
 *
 * @param string $reporter The reporter class to use.
 * @return void
 */
	public function handleReporter($reporter) {
		$object = null;

		$reporter = ucwords($reporter);
		$coreClass = 'Cake' . $reporter . 'Reporter';
		App::uses($coreClass, 'TestSuite/Reporter');

		$appClass = $reporter . 'Reporter';
		App::uses($appClass, 'TestSuite/Reporter');

		if (!class_exists($appClass)) {
			$object = new $coreClass(null, $this->_params);
		} else {
			$object = new $appClass(null, $this->_params);
		}
		return $this->arguments['printer'] = $object;
	}

}
