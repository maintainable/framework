<?php
/**
 * @category   Mad
 * @package    Mad_Model
 * @subpackage UnitTests
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt 
 */

/**
 * Set environment
 */
if (!defined('MAD_ENV')) define('MAD_ENV', 'test');
if (!defined('MAD_ROOT')) {
    require_once dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/config/environment.php';
}

/**
 * @group      model
 * @category   Mad
 * @package    Mad_Model
 * @subpackage UnitTests
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_Model_ConnectionAdapter_Abstract_ColumnTest extends Mad_Test_Unit
{
    /*##########################################################################
    # Construction
    ##########################################################################*/

    public function testDefaultNull()
    {
        $col = new Mad_Model_ConnectionAdapter_Mysql_Column('name', 'NULL', 'varchar(255)');
        $this->assertEquals(true, $col->isNull());
    }

    public function testNotNull()
    {
        $col = new Mad_Model_ConnectionAdapter_Mysql_Column('name', 'NULL', 'varchar(255)', false);
        $this->assertEquals(false, $col->isNull());
    }

    public function testName()
    {
        $col = new Mad_Model_ConnectionAdapter_Mysql_Column('name', 'NULL', 'varchar(255)');
        $this->assertEquals('name', $col->isNull());
    }

    public function testSqlType()
    {
        $col = new Mad_Model_ConnectionAdapter_Mysql_Column('name', 'NULL', 'varchar(255)');
        $this->assertEquals('varchar(255)', $col->getSqlType());
    }
    
    public function testIsText()
    {
        $col = new Mad_Model_ConnectionAdapter_Mysql_Column('test', 'NULL', 'varchar(255)');
        $this->assertTrue($col->isText());
        $col = new Mad_Model_ConnectionAdapter_Mysql_Column('test', 'NULL', 'text');
        $this->assertTrue($col->isText());

        $col = new Mad_Model_ConnectionAdapter_Mysql_Column('test', 'NULL', 'int(11)');
        $this->assertFalse($col->isText());
        $col = new Mad_Model_ConnectionAdapter_Mysql_Column('test', 'NULL', 'float(11,1)');
        $this->assertFalse($col->isText());
    }
    
    public function testIsNumber()
    {
        $col = new Mad_Model_ConnectionAdapter_Mysql_Column('test', 'NULL', 'varchar(255)');
        $this->assertFalse($col->isNumber());
        $col = new Mad_Model_ConnectionAdapter_Mysql_Column('test', 'NULL', 'text');
        $this->assertFalse($col->isNumber());

        $col = new Mad_Model_ConnectionAdapter_Mysql_Column('test', 'NULL', 'int(11)');
        $this->assertTrue($col->isNumber());
        $col = new Mad_Model_ConnectionAdapter_Mysql_Column('test', 'NULL', 'float(11,1)');
        $this->assertTrue($col->isNumber());
    }


    /*##########################################################################
    # Types
    ##########################################################################*/

    public function testTypeInteger()
    {
        $col = new Mad_Model_ConnectionAdapter_Mysql_Column('age', 'NULL', 'int(11)');
        $this->assertEquals('integer', $col->getType());
    }

    public function testTypeFloat()
    {
        $col = new Mad_Model_ConnectionAdapter_Mysql_Column('age', 'NULL', 'float(11,1)');
        $this->assertEquals('float', $col->getType());
    }

    public function testTypeDecimalPrecisionNone()
    {
        $col = new Mad_Model_ConnectionAdapter_Mysql_Column('age', 'NULL', 'decimal(11,0)');
        $this->assertEquals('integer', $col->getType());
    }

    public function testTypeDecimal()
    {
        $col = new Mad_Model_ConnectionAdapter_Mysql_Column('age', 'NULL', 'decimal(11,1)');
        $this->assertEquals('decimal', $col->getType());
    }

    public function testTypeDatetime()
    {
        $col = new Mad_Model_ConnectionAdapter_Mysql_Column('age', 'NULL', 'datetime');
        $this->assertEquals('datetime', $col->getType());
    }

    public function testTypeTimestamp()
    {
        $col = new Mad_Model_ConnectionAdapter_Mysql_Column('age', 'CURRENT_TIMESTAMP', 'timestamp');
        $this->assertEquals('timestamp', $col->getType());
    }

    public function testTypeTime()
    {
        $col = new Mad_Model_ConnectionAdapter_Mysql_Column('age', 'NULL', 'time');
        $this->assertEquals('time', $col->getType());
    }

    public function testTypeDate()
    {
        $col = new Mad_Model_ConnectionAdapter_Mysql_Column('age', 'NULL', 'date');
        $this->assertEquals('date', $col->getType());
    }

    public function testTypeText()
    {
        $col = new Mad_Model_ConnectionAdapter_Mysql_Column('age', 'NULL', 'text');
        $this->assertEquals('text', $col->getType());
    }

    public function testTypeBinary()
    {
        $col = new Mad_Model_ConnectionAdapter_Mysql_Column('age', 'NULL', 'blob(255)');
        $this->assertEquals('binary', $col->getType());
    }

    public function testTypeString()
    {
        $col = new Mad_Model_ConnectionAdapter_Mysql_Column('name', 'NULL', 'varchar(255)');
        $this->assertEquals('string', $col->getType());
    }

    /*##########################################################################
    # Primary
    ##########################################################################*/

    public function testPrimary()
    {
        $col = new Mad_Model_ConnectionAdapter_Mysql_Column('id', 'NULL', 'int(11)');
        $this->assertFalse($col->isPrimary());

        $col->setPrimary(true);
        $this->assertTrue($col->isPrimary());
    }

    /*##########################################################################
    # Human Name
    ##########################################################################*/

    public function testGetHumanNameSingleWord()
    {
        $col = new Mad_Model_ConnectionAdapter_Mysql_Column('name', 'NULL', 'varchar(255)');
        $this->assertEquals('Name', $col->getHumanName());
    }

    public function testGetHumanNameMultipleWords()
    {
        $col = new Mad_Model_ConnectionAdapter_Mysql_Column('team_alias_id', 'NULL', 'int(11)');
        $this->assertEquals('Team alias', $col->getHumanName());
    }


    /*##########################################################################
    # Extract Limit
    ##########################################################################*/

    public function testExtractLimitInt()
    {
        $col = new Mad_Model_ConnectionAdapter_Mysql_Column('test', 'NULL', 'int(11)');
        $this->assertEquals(11, $col->getLimit());
    }

    public function testExtractLimitVarchar()
    {
        $col = new Mad_Model_ConnectionAdapter_Mysql_Column('test', 'NULL', 'varchar(255)');
        $this->assertEquals(255, $col->getLimit());
    }

    public function testExtractLimitDecimal()
    {
        $col = new Mad_Model_ConnectionAdapter_Mysql_Column('test', 'NULL', 'decimal(11,1)');
        $this->assertEquals('11', $col->getLimit());
    }

    public function testExtractLimitText()
    {
        $col = new Mad_Model_ConnectionAdapter_Mysql_Column('test', 'NULL', 'text');
        $this->assertEquals(null, $col->getLimit());
    }

    public function testExtractLimitNone()
    {
        $col = new Mad_Model_ConnectionAdapter_Mysql_Column('test', 'NULL');
        $this->assertEquals(null, $col->getLimit());
    }

    /*##########################################################################
    # Extract Precision/Scale
    ##########################################################################*/

    public function testExtractPrecisionScale()
    {
        $col = new Mad_Model_ConnectionAdapter_Mysql_Column('test', 'NULL', 'decimal(12,1)');
        $this->assertEquals('12', $col->precision());
        $this->assertEquals('1',  $col->scale());
    }


    /*##########################################################################
    # Type Cast Values
    ##########################################################################*/

    public function testTypeCastInteger()
    {
        $col = new Mad_Model_ConnectionAdapter_Mysql_Column('name', '1', 'int(11)', false);
        $this->assertEquals(1, $col->getDefault());
    }

    public function testTypeCastFloat()
    {
        $col = new Mad_Model_ConnectionAdapter_Mysql_Column('version', '1.0', 'float(11,1)', false);
        $this->assertEquals(1.0, $col->getDefault());
    }

    public function testTypeCastString()
    {
        $col = new Mad_Model_ConnectionAdapter_Mysql_Column('name', 'n/a', 'varchar(255)', false);
        $this->assertEquals('n/a', $col->getDefault());
    }

    /**
     * @todo We may want to revisit this and cast default values back
     * to real boolean types.  In PHP, it doesn't matter as much.
     */
    public function testDoesNotTypeCastBooleanFalse()
    {
        $col = new Mad_Model_ConnectionAdapter_Mysql_Column('is_active', '0', 'tinyint(1)', false);
        $this->assertSame('0', $col->getDefault());
    }

    /**
     * @todo We may want to revisit this and cast default values back
     * to real boolean types.  In PHP, it doesn't matter as much.
     */
    public function testDoesNotTypeCastBooleanTrue()
    {
        $col = new Mad_Model_ConnectionAdapter_Mysql_Column('is_active', '1', 'tinyint(1)', false);
        $this->assertSame('1', $col->getDefault());
    }


    /*##########################################################################
    ##########################################################################*/
}