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
class Mad_Model_ConnectionAdapter_Mysql_ColumnTest extends Mad_Test_Unit
{
    /*##########################################################################
    # Column Types
    ##########################################################################*/

    public function testColumnTypeEnum()
    {
        $col = new Mad_Model_ConnectionAdapter_Mysql_Column('user', 'NULL', "enum('derek', 'mike')");
        $this->assertEquals('string', $col->getType());
    }

    public function testColumnTypeBoolean()
    {
        $col = new Mad_Model_ConnectionAdapter_Mysql_Column('is_active', 'NULL', 'tinyint(1)');
        $this->assertEquals('boolean', $col->getType());
    }


    /*##########################################################################
    # Defaults
    ##########################################################################*/

    public function testDefaultDatetime()
    {
        $col = new Mad_Model_ConnectionAdapter_Mysql_Column('name', '', 'datetime');
        $this->assertEquals(null, $col->getDefault());
    }

    public function testDefaultInteger()
    {
        $col = new Mad_Model_ConnectionAdapter_Mysql_Column('name', '', 'int(11)');
        $this->assertEquals(null, $col->getDefault());
    }

    public function testDefaultString()
    {
        $col = new Mad_Model_ConnectionAdapter_Mysql_Column('name', '', 'varchar(255)');
        $this->assertEquals('', $col->getDefault());
    }

    public function testDefaultText()
    {
        $col = new Mad_Model_ConnectionAdapter_Mysql_Column('name', '', 'text');
        $this->assertEquals('', $col->getDefault());
    }

    public function testDefaultBinary()
    {
        $col = new Mad_Model_ConnectionAdapter_Mysql_Column('name', '', 'blob(255)');
        $this->assertEquals('', $col->getDefault());
    }


    /*##########################################################################
    ##########################################################################*/

    public function testTruth() 
    {
        $this->assertTrue(true);
    }
}