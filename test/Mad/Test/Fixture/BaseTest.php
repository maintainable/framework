<?php
/**
 * @category   Mad
 * @package    Mad_Test
 * @subpackage UnitTests
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
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
 * @category   Mad
 * @package    Mad_Test
 * @subpackage UnitTests
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Test_Fixture_BaseTest extends Mad_Test_Unit
{
    public function setUp()
    {
        $this->_conn->execute("DELETE FROM unit_tests");
        Mad_Test_Fixture_Base::resetParsed();
    }


    /*##########################################################################
    # Construction
    ##########################################################################*/

    // test creating a fixture & save
    public function testNewFixtureA()
    {
        $fixture = new Mad_Test_Fixture_Base($this->_conn, 'unit_tests');
        $this->assertTrue($fixture instanceof Mad_Test_Fixture_Base);
    }

    // test creating a fixture not named after the db
    public function testNewFixtureB()
    {
        $fixture = new Mad_Test_Fixture_Base($this->_conn, 'unit_tests_more');
        $this->assertTrue($fixture instanceof Mad_Test_Fixture_Base);
    }

    // test creating a fixture that doesn't exist
    public function testNewFixtureInvalid()
    {
        $e = null;
        try {
            $fixture = new Mad_Test_Fixture_Base($this->_conn, 'does_not_exist');
        } catch (Exception $e) {}

        $this->assertTrue($e instanceof Mad_Test_Exception);
    }

    /*##########################################################################
    # Getting Fixture
    ##########################################################################*/
    
    public function testShouldGetYamlFilename()
    {
        $fixture = new Mad_Test_Fixture_Base($this->_conn, 'unit_tests');
        $this->assertEquals('unit_tests', $fixture->getYmlName());
    }
    
    public function testShouldGetTableName()
    {
        $fixture = new Mad_Test_Fixture_Base($this->_conn, 'unit_tests');
        $this->assertEquals('unit_tests', $fixture->getTableName());
    
        $fixture = new Mad_Test_Fixture_Base($this->_conn, 'unit_tests_more');
        $this->assertEquals('unit_tests', $fixture->getTableName());
    }

    /*##########################################################################
    # Getting records
    ##########################################################################*/

    // test loading fixtures
    public function testLoad()
    {
        $fixture = new Mad_Test_Fixture_Base($this->_conn, 'unit_tests');
        $this->assertEquals('0', $this->_countRecords());

        $fixture->load();
        $this->assertEquals('6', $this->_countRecords());
    }

    // test counting records created by the fixture
    public function testCountRecordsA()
    {
        $fixture = new Mad_Test_Fixture_Base($this->_conn, 'unit_tests');
        $fixture->load();
        $records = $fixture->getRecords();
        $this->assertEquals(6, count($records));
    }

    // test getting records created by the fixture
    public function testGetRecordsA()
    {
        $fixture = new Mad_Test_Fixture_Base($this->_conn, 'unit_tests');
        $fixture->load();
        $records = $fixture->getRecords();
        
        $expected = array(
            'id'             => '4',
            'integer_value'  => '4',
            'string_value'   => 'name d',
            'text_value'     => 'string b',
            'float_value'    => '1.2',
            'decimal_value'  => '1.2',
            'datetime_value' => '2005-12-23 12:34:23',
            'date_value'     => '2005-12-23',
            'time_value'     => '12:34:23',
            'blob_value'     => 'some blob data',
            'boolean_value'  => '0',
            'enum_value'     => 'b',
            'email_value'    => 'foo@example.com'
        );
        $this->assertEquals($expected, $records['unit_test_4']);
    }

    // test getting a record created by the fixture
    public function testGetRecordA()
    {
        $fixture = new Mad_Test_Fixture_Base($this->_conn, 'unit_tests');
        $fixture->load();
        $records = $fixture->getRecord('unit_test_1');
        $this->assertEquals('name a', $records['string_value']);
    }

    // test that we actually loaded the data
    public function testDataInsertedCntA()
    {
        $fixture = new Mad_Test_Fixture_Base($this->_conn, 'unit_tests');
        $fixture->load();

        $this->assertEquals('6', $this->_countRecords());
    }

    // test that we actually loaded the data
    public function testDataInsertedA()
    {
        $fixture = new Mad_Test_Fixture_Base($this->_conn, 'unit_tests');
        $fixture->load();

        $this->assertTrue($this->_recordExists(3));
    }

    // test counting records created by the fixture with named table
    public function testCountRecordsB()
    {
        $fixture = new Mad_Test_Fixture_Base($this->_conn, 'unit_tests_more');
        $fixture->load();
        $records = $fixture->getRecords();
        $this->assertTrue(count($records) > 2);
    }


    /*##########################################################################
    # Fixtures that specify a table
    ##########################################################################*/

    // test getting records created by the fixture with named table
    public function testGetRecordsB()
    {
        $fixture = new Mad_Test_Fixture_Base($this->_conn, 'unit_tests_more');
        $fixture->load();
        $records = $fixture->getRecords();

        $expected = array(
            'id'             => '9',
            'integer_value'  => '9',
            'string_value'   => 'name h',
            'text_value'     => 'string b',
            'float_value'    => '1.2',
            'decimal_value'  => '1.2',
            'datetime_value' => '2005-12-23 12:34:23',
            'date_value'     => '2005-12-23',
            'time_value'     => '12:34:23',
            'blob_value'     => 'some blob data',
            'boolean_value'  => '1',
            'enum_value'     => 'a',
            'email_value'    => 'foo@example.com'
        );
        $this->assertEquals($expected, $records['unit_test_9']);
    }

    // test getting a record created by the fixture with named table
    public function testGetRecordB()
    {
        $fixture = new Mad_Test_Fixture_Base($this->_conn, 'unit_tests_more');
        $fixture->load();
        $records = $fixture->getRecord('unit_test_7');
        $this->assertEquals('name f', $records['string_value']);
    }

    // test that we actually loaded the data with named table
    public function testDataInsertedCntB()
    {
        $fixture = new Mad_Test_Fixture_Base($this->_conn, 'unit_tests_more');
        $fixture->load();

        $this->assertTrue($this->_countRecords() >= 5);
    }

    // test that we actually loaded the data with named table
    public function testDataInsertedB()
    {
        $fixture = new Mad_Test_Fixture_Base($this->_conn, 'unit_tests_more');
        $fixture->load();

        $id = $this->_conn->selectValue("SELECT id FROM unit_tests WHERE id=3");

        $this->assertTrue($this->_recordExists(6));
    }


    /*##########################################################################
    # Test blob data
    ##########################################################################*/

    //test that the clob got set correct
    public function testBlobArray()
    {
        $blobStr = trim(str_repeat("some clob data ", 519));
        $fixture = new Mad_Test_Fixture_Base($this->_conn, 'unit_tests');
        $fixture->load();
        $record = $fixture->getRecord('unit_test_6');
        $this->assertEquals($blobStr, $record['blob_value']);
    }

    //test that the clob inserted correctly
    public function testBlobInsert()
    {
        $fixture = new Mad_Test_Fixture_Base($this->_conn, 'unit_tests');
        $fixture->load();
        $record = $fixture->getRecord('unit_test_6');

        $ut = $this->_getRecord(6);
        $this->assertEquals($record['blob_value'], $ut['blob_value']);
    }

    //test that the clob inserted correctly
    public function testDynamicFixture()
    {
        $fixture = new Mad_Test_Fixture_Base($this->_conn, 'unit_tests');
        $fixture->load();
        $record = $fixture->getRecord('unit_test_2');
        
        $this->assertEquals(date("Y-m-d"), $record['date_value']);
    }

    /*##########################################################################
    # Test fixtures that specify other required fixutres
    ##########################################################################*/

    // test getting the record names from the diff yml file
    public function testRequireGetRecords()
    {
        $fixture = new Mad_Test_Fixture_Base($this->_conn, 'unit_tests_more');
        $fixture->load();
        $records = $fixture->getRecords();

        $expected = array(
            'id'             => '1',
            'integer_value'  => '1',
            'string_value'   => 'name a',
            'text_value'     => 'string a',
            'float_value'    => '1.2',
            'decimal_value'  => '1.2',
            'datetime_value' => '2005-12-23 12:34:23',
            'date_value'     => '2005-12-23',
            'time_value'     => '12:34:23',
            'blob_value'     => 'some blob data',
            'boolean_value'  => '1',
            'enum_value'     => 'a', 
            'email_value'    => 'foo@example.com'
        );
        $this->assertEquals($expected, $records['unit_test_1']);
    }


    /*##########################################################################
    # Test executing sql before/after loading fixture data
    ##########################################################################*/

    // test exexuting sql
    public function testBefore()
    {
        $fixture = new Mad_Test_Fixture_Base($this->_conn, 'unit_tests_opts');
        $fixture->load();
        $this->assertTrue($this->_recordExists(100));
    }

    // test exexuting sql
    public function testAfter()
    {
        $fixture = new Mad_Test_Fixture_Base($this->_conn, 'unit_tests_opts');
        $fixture->load();
        $this->assertTrue($this->_recordExists(101));
    }


    /*##########################################################################
    # Test static methods
    ##########################################################################*/

    // get parsed yaml
    public function testGetParsed()
    {
        $fixture = new Mad_Test_Fixture_Base($this->_conn, 'unit_tests_more');

        $expected = array('unit_tests' => 1, 'unit_tests_more' => 1);
        $this->assertEquals($expected, $fixture->getParsed());
    }

    // reset parsed list
    public function testResetParsed()
    {
        $fixture = new Mad_Test_Fixture_Base($this->_conn, 'unit_tests_more');

        $expected = array('unit_tests' => 1, 'unit_tests_more' => 1);
        $this->assertEquals($expected, $fixture->getParsed());

        $fixture->resetParsed();
        $this->assertEquals(array(), $fixture->getParsed());
    }

    public function testGetToLoad()
    {
        $fixture = new Mad_Test_Fixture_Base($this->_conn, 'unit_tests_more');

        $expected = array('unit_tests' => 1, 'unit_tests_more' => 1);
        $this->assertEquals($expected, $fixture->getToLoad());

        $fixture->load();
        $this->assertEquals(array(), $fixture->getToLoad());
    }

    public function testResetToLoad()
    {
        $fixture = new Mad_Test_Fixture_Base($this->_conn, 'unit_tests_more');
        $fixture->load();
        $this->assertEquals(array(), $fixture->getToLoad());

        $fixture->resetToLoad();
        $expected = array('unit_tests' => 1, 'unit_tests_more' => 1);
        $this->assertEquals($expected, $fixture->getToLoad());
    }

    public function testGetToTeardown()
    {
        $fixture = new Mad_Test_Fixture_Base($this->_conn, 'unit_tests_more');

        $expected = array('unit_tests' => 1, 'unit_tests_more' => 1);
        $this->assertEquals($expected, $fixture->getToTeardown());

        $fixture->teardown();
        $expected = array('unit_tests' => 0, 'unit_tests_more' => 0);
        $this->assertEquals($expected, $fixture->getToTeardown());
    }

    public function testResetToTeardown()
    {
        $fixture = new Mad_Test_Fixture_Base($this->_conn, 'unit_tests_more');

        $fixture->teardown();
        $expected = array('unit_tests' => 0, 'unit_tests_more' => 0);
        $this->assertEquals($expected, $fixture->getToTeardown());

        $fixture->resetToTeardown();
        $expected = array('unit_tests' => 1, 'unit_tests_more' => 1);
        $this->assertEquals($expected, $fixture->getToTeardown());
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
