<?php
/**
 * @category   Mad
 * @package    Mad_Model
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
 * @group      model
 * @category   Mad
 * @package    Mad_Model
 * @subpackage UnitTests
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Model_Join_BaseTest extends Mad_Test_Unit
{
    // model obj
    public function testGetModel()
    {
        $join = new Mad_Model_Join_Base(new UnitTest);
        $this->assertTrue($join->model() instanceof UnitTest);
    }

    // prefix for join table column aliases
    public function testAliasedPrefix()
    {
        $join = new Mad_Model_Join_Base(new UnitTest);
        $this->assertEquals('T0', $join->aliasedPrefix());
    }

    // alias for pk
    public function testAliasedPrimaryKey()
    {
        $join = new Mad_Model_Join_Base(new UnitTest);
        $this->assertEquals('T0_R0', $join->aliasedPrimaryKey());
    }

    public function testAliasedTableName()
    {
        $join = new Mad_Model_Join_Base(new UnitTest);
        $this->assertEquals('unit_tests', $join->aliasedTableName());
    }

    // getting col names with aliases for select
    public function testColumnNamesWithAliasForSelect()
    {
        $join = new Mad_Model_Join_Base(new UnitTest);
        $expected = array(
            array('unit_tests.id',             'T0_R0'),
            array('unit_tests.integer_value',  'T0_R1'),
            array('unit_tests.string_value',   'T0_R2'),
            array('unit_tests.text_value',     'T0_R3'),
            array('unit_tests.float_value',    'T0_R4'),
            array('unit_tests.decimal_value',  'T0_R5'),
            array('unit_tests.datetime_value', 'T0_R6'),
            array('unit_tests.date_value',     'T0_R7'),
            array('unit_tests.time_value',     'T0_R8'),
            array('unit_tests.blob_value',     'T0_R9'),
            array('unit_tests.boolean_value',  'T0_R10'),
            array('unit_tests.enum_value',     'T0_R11'),
            array('unit_tests.email_value',    'T0_R12')
        );
        $this->assertEquals($expected, $join->columnNamesWithAliasForSelect());
    }

    // getting col names with aliases
    public function testColumnNamesWithAliasForExtract()
    {
        $join = new Mad_Model_Join_Base(new UnitTest);
        $expected = array(
            array('id',             'T0_R0'),
            array('integer_value',  'T0_R1'),
            array('string_value',   'T0_R2'),
            array('text_value',     'T0_R3'),
            array('float_value',    'T0_R4'),
            array('decimal_value',  'T0_R5'),
            array('datetime_value', 'T0_R6'),
            array('date_value',     'T0_R7'),
            array('time_value',     'T0_R8'),
            array('blob_value',     'T0_R9'),
            array('boolean_value',  'T0_R10'),
            array('enum_value',     'T0_R11'), 
            array('email_value',    'T0_R12')
        );
        $this->assertEquals($expected, $join->columnNamesWithAliasForExtract());
    }

    // extracting a record from the db row data
    public function testExtractRecord()
    {
        $join = new Mad_Model_Join_Base(new UnitTest);
        $row = array(
            'T0_R0'  => '1',
            'T0_R1'  => '1',
            'T0_R2'  => 'string value',
            'T0_R3'  => 'text value',
            'T0_R4'  => '1.2',
            'T0_R5'  => '1.2',
            'T0_R6'  => '2005-12-23 12:34:23',
            'T0_R7'  => '2005-12-23',
            'T0_R8'  => '12:34:23 ',
            'T0_R9'  => 'some blob data',
            'T0_R10' => '1',
            'T0_R11' => 'b',
            'T0_R12' => 'foo@example.com',
            'T1_R0'  => 'test more data',
            'T1_R1'  => 'test another col');
        $expected = array(
            'id'             => '1',
            'integer_value'  => '1',
            'string_value'   => 'string value',
            'text_value'     => 'text value',
            'float_value'    => '1.2',
            'decimal_value'  => '1.2',
            'datetime_value' => '2005-12-23 12:34:23',
            'date_value'     => '2005-12-23',
            'time_value'     => '12:34:23 ',
            'blob_value'     => 'some blob data',
            'boolean_value'  => '1',
            'enum_value'     => 'b', 
            'email_value'    => 'foo@example.com');
        $this->assertEquals($expected, $join->extractRecord($row));
    }

    // getting the record id (pk value) from db row data
    public function testRecordId()
    {
        $join = new Mad_Model_Join_Base(new UnitTest);
        $row = array(
            'T0_R0'  => '1',
            'T0_R1'  => '1',
            'T0_R2'  => 'string value',
            'T0_R3'  => 'text value',
            'T0_R4'  => '1.2',
            'T0_R5'  => '1.2',
            'T0_R6'  => '2005-12-23 12:34:23',
            'T0_R7'  => '2005-12-23',
            'T0_R8'  => '12:34:23 ',
            'T0_R9'  => 'some blob data',
            'T0_R10' => '1',
            'T0_R11' => 'b',
            'T0_R12' => 'foo@example.com',
            'T1_R0'  => 'test more data',
            'T1_R1'  => 'test another col');
        $this->assertEquals('1', $join->recordId($row));
    }

    // instantiating the object from the row
    public function testInstantiate()
    {
        $join = new Mad_Model_Join_Base(new UnitTest);
        $row = array(
            'T0_R0'  => '1',
            'T0_R1'  => '1',
            'T0_R2'  => 'string value',
            'T0_R3'  => 'text value',
            'T0_R4'  => '1.2',
            'T0_R5'  => '1.2',
            'T0_R6'  => '2005-12-23 12:34:23',
            'T0_R7'  => '2005-12-23',
            'T0_R8'  => '12:34:23 ',
            'T0_R9'  => 'some blob data',
            'T0_R10' => '1',
            'T0_R11' => 'b',
            'T0_R12' => 'foo@example.com',
            'T1_R0'  => 'test more data',
            'T1_R1'  => 'test another col');
        $record = $join->extractRecord($row);

        $this->assertEquals('1', $join->instantiate($row)->id);
        $this->assertEquals('1', $join->instantiate($row)->integer_value);
        $this->assertEquals('1', $join->instantiate($row)->boolean_value);
    }


    /*##########################################################################
    ##########################################################################*/
}