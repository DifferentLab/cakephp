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
 * @since         CakePHP(tm) v 2.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

use PHPUnit\Runner\AfterLastTestHook;
use PHPUnit\Runner\BeforeFirstTestHook;

if (class_exists('SebastianBergmann\CodeCoverage\CodeCoverage')) {
	class_alias('SebastianBergmann\CodeCoverage\Report\Text', 'PHP_CodeCoverage_Report_Text');
	class_alias('SebastianBergmann\CodeCoverage\Report\PHP', 'PHP_CodeCoverage_Report_PHP');
	class_alias('SebastianBergmann\CodeCoverage\Report\Clover', 'PHP_CodeCoverage_Report_Clover');
	class_alias('SebastianBergmann\CodeCoverage\Report\Html\Facade', 'PHP_CodeCoverage_Report_HTML');
	class_alias('SebastianBergmann\CodeCoverage\Exception', 'PHP_CodeCoverage_Exception');
}

App::uses('CakeFixtureManager', 'TestSuite/Fixture');

/**
 * A custom test runner for CakePHP's use of PHPUnit.
 *
 * @property array $_params
 * @package       Cake.TestSuite
 */
class CakeTestHook implements BeforeFirstTestHook, AfterLastTestHook
{

    private                         $fixtureManager;

    private \PHPUnit\Framework\Test $suite;

    /**
     * Lets us pass in some options needed for CakePHP's webrunner.
     *
     * @param \PHPUnit\Framework\Test $suite
     * @param string                  $fixtureManager
     */
    public function __construct(PHPUnit\Framework\Test $suite, string $fixtureManager = '')
    {
        $this->fixtureManager = $this->getFixtureManager($fixtureManager);
        $this->suite          = $suite;
    }

    /**
     * Get the fixture manager class specified or use the default one.
     *
     * @param array $arguments The CLI arguments.
     *
     * @return mixed instance of a fixture manager.
     * @throws RuntimeException When fixture manager class cannot be loaded.
     */
    private function getFixtureManager(string $fixtureManager)
    {
        if (!empty($fixtureManager)) {
            App::uses($fixtureManager, 'TestSuite');
            if (class_exists($fixtureManager)) {
                return new $fixtureManager;
            }
            throw new RuntimeException(__d('cake_dev', 'Could not find fixture manager %s.', $fixtureManager));
        }
        App::uses('AppFixtureManager', 'TestSuite');
        if (class_exists('AppFixtureManager')) {
            return new AppFixtureManager();
        }

        return new CakeFixtureManager();
    }

    public function executeBeforeFirstTest(): void
    {
        $iterator = $this->suite->getIterator();
        if ($iterator instanceof RecursiveIterator) {
            $iterator = new RecursiveIteratorIterator($iterator);
        }
        foreach ($iterator as $test) {
            if ($test instanceof CakeTestCase) {
                $this->fixtureManager->fixturize($test);
                $test->fixtureManager = $this->fixtureManager;
            }
        }
    }

    public function executeAfterLastTest(): void
    {
        $this->fixtureManager->shutDown();
    }

}
