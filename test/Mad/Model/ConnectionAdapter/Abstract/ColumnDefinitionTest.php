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
    require_once dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/config/environment.php';
}

/**
 * @group      model
 * @category   Mad
 * @package    Mad_Model
 * @subpackage UnitTests
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Model_ConnectionAdapter_Abstract_ColumnDefinitionTest extends Mad_Test_Unit
{
    public function testConstruct() 
    {
        $col = new Mad_Model_ConnectionAdapter_Abstract_ColumnDefinition(
            $this->_conn, 'col_name', 'string'
        );
        $this->assertEquals('col_name', $col->getName());
        $this->assertEquals('string',   $col->getType());
    }
    
    public function testToSql() 
    {
        $col = new Mad_Model_ConnectionAdapter_Abstract_ColumnDefinition(
            $this->_conn, 'col_name', 'string'
        );
        $this->assertEquals('`col_name` varchar(255)', $col->toSql());
    }

    public function testToSqlLimit() 
    {
        $col = new Mad_Model_ConnectionAdapter_Abstract_ColumnDefinition(
            $this->_conn, 'col_name', 'string', 40
        );
        $this->assertEquals('`col_name` varchar(40)', $col->toSql());

        // set attribute instead
        $col = new Mad_Model_ConnectionAdapter_Abstract_ColumnDefinition(
            $this->_conn, 'col_name', 'string'
        );
        $col->setLimit(40);
        $this->assertEquals('`col_name` varchar(40)', $col->toSql());
    }

    public function testToSqlPrecisionScale() 
    {
        $col = new Mad_Model_ConnectionAdapter_Abstract_ColumnDefinition(
            $this->_conn, 'col_name', 'decimal', null, 5, 2
        );
        $this->assertEquals('`col_name` decimal(5, 2)', $col->toSql());

        // set attribute instead
        $col = new Mad_Model_ConnectionAdapter_Abstract_ColumnDefinition(
            $this->_conn, 'col_name', 'decimal'
        );
        $col->setPrecision(5);
        $col->setScale(2);
        $this->assertEquals('`col_name` decimal(5, 2)', $col->toSql());
    }

    public function testToSqlNotNull() 
    {
        $col = new Mad_Model_ConnectionAdapter_Abstract_ColumnDefinition(
            $this->_conn, 'col_name', 'string', null, null, null, null, false
        );
        $this->assertEquals('`col_name` varchar(255) NOT NULL', $col->toSql());

        // set attribute instead
        $col = new Mad_Model_ConnectionAdapter_Abstract_ColumnDefinition(
            $this->_conn, 'col_name', 'string'
        );
        $col->setNull(false);
        $this->assertEquals('`col_name` varchar(255) NOT NULL', $col->toSql());
    }

  public function testToSqlDefault() 
  {
      $col = new Mad_Model_ConnectionAdapter_Abstract_ColumnDefinition(
          $this->_conn, 'col_name', 'string', null, null, null, 'test', null
      );
      $this->assertEquals("`col_name` varchar(255) DEFAULT 'test'", $col->toSql());

      // set attribute instead
      $col = new Mad_Model_ConnectionAdapter_Abstract_ColumnDefinition(
          $this->_conn, 'col_name', 'string'
      );
      $col->setDefault('test');
      $this->assertEquals("`col_name` varchar(255) DEFAULT 'test'", $col->toSql());
  }
}