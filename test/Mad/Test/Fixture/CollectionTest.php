<?php
/**
 * @category   Mad
 * @package    Mad_Test
 * @subpackage UnitTests
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD 
 */

/**
 * Set environment
 */
if (!defined('MAD_ENV')) define('MAD_ENV', 'test');
if (!defined('MAD_ROOT')) {
    require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config/environment.php';
}

/**
 * @group      test
 * @category   Mad
 * @package    Mad_Test
 * @subpackage UnitTests
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Test_Fixture_CollectionTest extends Mad_Test_Unit
{
    public function setUp()
    {
        $this->_conn->execute("DELETE FROM unit_tests");
        Mad_Test_Fixture_Base::resetParsed();
    }

    // test loading single fixture
    public function testConstructSingle()
    {
        $fixtures = new Mad_Test_Fixture_Collection($this->_conn, 'unit_tests');

        $expected = array('unit_tests' => 1);
        $this->assertEquals($expected, Mad_Test_Fixture_Base::getParsed());
    }

    // test loading multiple fixtures at once
    public function testConstructMultiple()
    {
        $fixtures = new Mad_Test_Fixture_Collection($this->_conn, array('unit_tests', 'unit_tests_opts'));

        $expected = array('unit_tests' => 1, 'unit_tests_opts' => 1);
        $this->assertEquals($expected, Mad_Test_Fixture_Base::getParsed());
    }

    // make sure the same fixture doesn't load twice
    public function testConstructMultipleDuplicate()
    {
        $fixtures = new Mad_Test_Fixture_Collection($this->_conn, array('unit_tests', 'unit_tests_more'));

        $expected = array('unit_tests' => 2, 'unit_tests_more' => 1);
        $this->assertEquals($expected, Mad_Test_Fixture_Base::getParsed());
    }

    // test loading more than once per request
    public function testConstructAgain()
    {
        $fixtures = new Mad_Test_Fixture_Collection($this->_conn, 'unit_tests');
        $fixtures = new Mad_Test_Fixture_Collection($this->_conn, 'unit_tests');
        $fixtures = new Mad_Test_Fixture_Collection($this->_conn, 'unit_tests');

        $expected = array('unit_tests' => 1);
        $this->assertEquals($expected, Mad_Test_Fixture_Base::getParsed());
    }

    // test adding fixtures
    public function testAddFixture()
    {
        $fixtures = new Mad_Test_Fixture_Collection($this->_conn, 'unit_tests');
        $fixtures->addFixture('unit_tests');

        $expected = array('unit_tests' => 2);
        $this->assertEquals($expected, Mad_Test_Fixture_Base::getParsed());
    }

    // should load during construction
    public function testLoad()
    {
        $fixtures = new Mad_Test_Fixture_Collection($this->_conn, 'unit_tests');
        $this->assertEquals('6', $this->_countRecords());
    }

    // test tearing down
    public function testTeardown()
    {
        $fixtures = new Mad_Test_Fixture_Collection($this->_conn, 'unit_tests');
        $fixtures->teardown();

        $this->assertEquals('0', $this->_countRecords());
    }

    // test getting records (make sure we get required data too)
    public function testGetRecords()
    {
        $fixtures = new Mad_Test_Fixture_Collection($this->_conn, 'unit_tests_more');
        $this->assertEquals(11, count($fixtures->getRecords()));
    }

    /*##########################################################################
    ##########################################################################*/


    protected function _countRecords() 
    {
        return $this->_conn->selectValue("SELECT COUNT(1) FROM unit_tests");
    }
    
    protected function _recordExists($id) 
    {
        $val = $this->_conn->selectValue("SELECT id FROM unit_tests WHERE id='$id'");
        return $id == $val;
    }
    
    protected function _getRecord($id) 
    {
        return $this->_conn->selectOne("SELECT * FROM unit_tests WHERE id='$id'");
    }
}

?>