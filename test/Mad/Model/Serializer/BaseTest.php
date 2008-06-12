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
    require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config/environment.php';
}

/**
 * @todo Tests for sanitizeSql()
 * 
 * @group      model
 * @category   Mad
 * @package    Mad_Model
 * @subpackage UnitTests
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_Model_Serializer_BaseTest extends Mad_Test_Unit
{
    // set up new db by inserting dummy data into the db
    public function setUp()
    {
        $this->fixtures('users', 'articles', 'comments');
    }

    public function testGetSerializableAttributeNames()
    {
        $record  = $this->users('mike');
        $options = array();

        $serializer = new Mad_Model_Serializer_Base($record, $options);      
        $attrNames = $serializer->getSerializableAttributeNames();

        $expected = array('approved', 'created_at', 'created_on', 'first_name', 
                          'id', 'name', 'updated_at', 'updated_on');
        $this->assertEquals($expected, $attrNames);
    }

    public function testGetSerializableAttributeNamesExceptSingle()
    {
        $record  = $this->users('mike');
        $options = array('except' => 'first_name');

        $serializer = new Mad_Model_Serializer_Base($record, $options);      
        $attrNames = $serializer->getSerializableAttributeNames();

        $expected = array('approved', 'created_at', 'created_on', 
                          'id', 'name', 'updated_at', 'updated_on');
        $this->assertEquals($expected, $attrNames);
    }

    public function testGetSerializableAttributeNamesExceptMultiple()
    {
        $record  = $this->users('mike');
        $options = array('except' => array('first_name', 'name'));

        $serializer = new Mad_Model_Serializer_Base($record, $options);      
        $attrNames = $serializer->getSerializableAttributeNames();

        $expected = array('approved', 'created_at', 'created_on', 
                          'id', 'updated_at', 'updated_on');
        $this->assertEquals($expected, $attrNames);
    }

    public function testGetSerializableAttributeNamesOnlySingle()
    {
        $record  = $this->users('mike');
        $options = array('only' => 'first_name');

        $serializer = new Mad_Model_Serializer_Base($record, $options);      
        $attrNames = $serializer->getSerializableAttributeNames();

        $expected = array('first_name');
        $this->assertEquals($expected, $attrNames);
    }

    public function testGetSerializableAttributeNamesOnlyMultiple()
    {
        $record  = $this->users('mike');
        $options = array('only' => array('first_name', 'name'));

        $serializer = new Mad_Model_Serializer_Base($record, $options);      
        $attrNames = $serializer->getSerializableAttributeNames();

        $expected = array('first_name', 'name');
        $this->assertEquals($expected, $attrNames);
    }

    public function testGetSerializableMethodNamesSingle()
    {
        $record  = $this->articles('xml_rpc');
        $options = array('methods' => 'foo');

        $serializer = new Mad_Model_Serializer_Base($record, $options);        
        $methodNames = $serializer->getSerializableMethodNames();
        
        $expected = array('foo');
    }

    public function testGetSerializableMethodNamesMultiple()
    {
        $record  = $this->articles('xml_rpc');
        $options = array('methods' => array('foo', 'bar'));

        $serializer = new Mad_Model_Serializer_Base($record, $options);        
        $methodNames = $serializer->getSerializableMethodNames();
        
        $expected = array('foo', 'bar');
    }

    public function testGetSerializableNames()
    {
        $record  = $this->articles('xml_rpc');
        $options = array('methods' => 'foo', 'except' => 'title');

        $serializer = new Mad_Model_Serializer_Base($record, $options);        
        $attrNames = $serializer->getSerializableNames();

        $expected = array('foo', 'id', 'user_id');
        $this->assertEquals($expected, $attrNames);
    }

    public function testGetSerializableRecord()
    {
        $record  = $this->articles('xml_rpc');
        $options = array();

        $serializer = new Mad_Model_Serializer_Base($record, $options);        
        $record = $serializer->getSerializableRecord();

        $expected = array (
          'id'      => '1',
          'title'   => 'Easier XML-RPC for PHP5',
          'user_id' => '1'
        );

        $this->assertEquals($expected, $record);
    }

    public function testGetSerializableRecordIncludeSingleBelongsto()
    {
        $record  = $this->articles('xml_rpc');
        $options = array('include' => 'User');

        $serializer = new Mad_Model_Serializer_Base($record, $options);        
        $record = $serializer->getSerializableRecord();

        $expected = array (
          'id'      => '1',
          'title'   => 'Easier XML-RPC for PHP5',
          'user_id' => '1', 
          'User'    => array(
                'approved' => 1, 
                'created_at' => '2008-01-01 12:20:00', 
                'created_on' => '2008-01-01', 
                'first_name' => 'Mike', 
                'id'         => 1, 
                'name'       => 'Mike Naberezny', 
                'updated_at' => '2008-01-01 12:20:00', 
                'updated_on' => '2008-01-01'
              )
        );
    
        $this->assertEquals($expected, $record);
    }

    public function testGetSerializableRecordIncludeSingleHasMany()
    {
        $record  = $this->articles('xml_rpc');
        $options = array('include' => 'Comments');

        $serializer = new Mad_Model_Serializer_Base($record, $options);        
        $record = $serializer->getSerializableRecord();

        $expected = array (
          'id'      => '1',
          'title'   => 'Easier XML-RPC for PHP5',
          'user_id' => '1', 
          'Comments' => array(
              array('article_id' => 1, 'body' => 'Comment A', 'id' => 1, 'user_id' => 1), 
              array('article_id' => 1, 'body' => 'Comment B', 'id' => 2, 'user_id' => 1)
           )
        );

        $this->assertEquals($expected, $record);
    }

    public function testGetSerializableRecordIncludeMultiple()
    {
        $record  = $this->articles('xml_rpc');
        $options = array('include' => array('User', 'Comments'));

        $serializer = new Mad_Model_Serializer_Base($record, $options);        
        $record = $serializer->getSerializableRecord();
        
        $expected = array (
          'id'      => '1',
          'title'   => 'Easier XML-RPC for PHP5',
          'user_id' => '1', 
          'User'    => array(
                'approved' => 1, 
                'created_at' => '2008-01-01 12:20:00', 
                'created_on' => '2008-01-01', 
                'first_name' => 'Mike', 
                'id'         => 1, 
                'name'       => 'Mike Naberezny', 
                'updated_at' => '2008-01-01 12:20:00', 
                'updated_on' => '2008-01-01'
              ), 
          'Comments' => array(
              array('article_id' => 1, 'body' => 'Comment A', 'id' => 1, 'user_id' => 1), 
              array('article_id' => 1, 'body' => 'Comment B', 'id' => 2, 'user_id' => 1)
           )
        );
        
        $this->assertEquals($expected, $record);
    }

    public function testGetSerializableRecordIncludeWithOptions()
    {
        $record  = $this->articles('xml_rpc');
        $options = array('include' => array('User'     => array('only'   => 'name'), 
                                            'Comments' => array('except' => 'article_id')));

        $serializer = new Mad_Model_Serializer_Base($record, $options);        
        $record = $serializer->getSerializableRecord();

        $expected = array (
          'id'      => '1',
          'title'   => 'Easier XML-RPC for PHP5',
          'user_id' => '1', 
          'User'    => array('name' => 'Mike Naberezny'), 
          'Comments' => array(
              array('body' => 'Comment A', 'id' => 1, 'user_id' => 1), 
              array('body' => 'Comment B', 'id' => 2, 'user_id' => 1)
           )
        );

        $this->assertEquals($expected, $record);
    }


    public function testGetSerializableRecordWithMethods()
    {
        $record  = $this->articles('xml_rpc');
        $options = array('methods' => array('foo', 'bar'));

        $serializer = new Mad_Model_Serializer_Base($record, $options);        
        $record = $serializer->getSerializableRecord();

        $expected = array (
          'id'      => '1',
          'title'   => 'Easier XML-RPC for PHP5',
          'user_id' => '1', 
          'foo'     => 'test serializer foo', 
          'bar'     => 'test serializer bar'
        );

        $this->assertEquals($expected, $record);
    }
}