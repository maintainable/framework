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
class Mad_Model_Serializer_JsonTest extends Mad_Test_Unit
{
    // set up new db by inserting dummy data into the db
    public function setUp()
    {
        $this->fixtures('users', 'articles', 'comments');
    }
    
    public function testSerialize()
    {
        $record  = $this->users('mike');
        $options = array('include' => array('Comments' => array('only' => 'body')), 
                         'only'    => 'name');

        $serializer = new Mad_Model_Serializer_Json($record, $options);
        
        $expected = '{"name":"Mike Naberezny",'.
                     '"Comments":[{"body":"Comment A"},{"body":"Comment B"}]}';

        $this->assertEquals($expected, $serializer->serialize());
    }

    public function testToJson()
    {
        $record  = $this->users('mike');
        $options = array('include' => array('Comments' => array('only' => 'body')), 
                         'only'    => 'name');
                     
        $expected = '{"name":"Mike Naberezny",'.
                     '"Comments":[{"body":"Comment A"},{"body":"Comment B"}]}';

        $this->assertEquals($expected, $record->toJson($options));
    }

    public function testToJsonIncludeRoot()
    {
        Mad_Model_Base::$includeRootInJson = true;

        $record  = $this->users('mike');
        $options = array('only' => 'name');

        $expected = '{ "user": {"name":"Mike Naberezny"} }';

        $this->assertEquals($expected, $record->toJson($options));
    }

    public function testFromJson()
    {
        $record = new Article;
        $json = '{"id":1,"title":"Easier XML-RPC for PHP5","user_id":1}';
        $article = $record->fromJson($json);
        
        $this->assertType('Article', $article);
        
        $this->assertEquals(1, $article->id);
        $this->assertEquals(1, $article->user_id);
        $this->assertEquals("Easier XML-RPC for PHP5", $article->title);
    }
}