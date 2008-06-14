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
class Mad_Model_Serializer_XmlTest extends Mad_Test_Unit
{
    // set up new db by inserting dummy data into the db
    public function setUp()
    {
        $this->fixtures('companies', 'users', 'articles', 'comments', 'unit_tests');
    }


    /*##########################################################################
    # Xml Serialization Test
    ##########################################################################*/

    public function testShouldSerializeDefaultRoot()
    {
        $user = new User;
        $xml  = $user->toXml();
        
        $this->assertContains('<user>',  $xml);
        $this->assertContains('</user>', $xml);
    }

    public function testShouldSerializeDefaultRootWithNamespace()
    {
        $user = new User;
        $xml  = $user->toXml(array('namespace' => "http://xml.rubyonrails.org/contact"));
                
        $this->assertContains('<user xmlns="http://xml.rubyonrails.org/contact">',  $xml);
        $this->assertContains('</user>', $xml);
    }

    public function testShouldSerializeCustomRoot()
    {
        $user = new User;
        $xml  = $user->toXml(array('root' => "xml_contact"));
        
        $this->assertContains('<xml-contact>',  $xml);
        $this->assertContains('</xml-contact>', $xml);
    }

    public function testShouldAllowUndasherizedTags()
    {
        $user = new User;
        $xml  = $user->toXml(array('root' => "xml_contact", 'dasherize' => false));

        $this->assertContains('<xml_contact>',  $xml);
        $this->assertContains('</xml_contact>', $xml);
        $this->assertContains('<created_at',    $xml);
    }


    /*##########################################################################
    # Default Xml Serialization Test
    ##########################################################################*/

    public function testShouldSerializeString()
    {
        $xml = User::find(1)->toXml();
        
        $this->assertContains('<name>Mike Naberezny</name>', $xml);
    }

    public function testShouldSerializeInteger()
    {
        $xml = User::find(1)->toXml();
        
        $this->assertContains('<id type="integer">1</id>', $xml);
    }

    public function testShouldSerializeBinary()
    {
        $xml = UnitTest::find(1)->toXml();

        $this->assertContains('c29tZSBibG9iIGRhdGE=</blob-value>',            $xml);
        $this->assertContains('<blob-value encoding="base64" type="binary">', $xml);
    }

    public function testShouldSerializeDate()
    {
        $xml = User::find(1)->toXml();

        $this->assertContains('<created-on type="date">2008-01-01</created-on>', $xml);
    }

    public function testShouldSerializeDatetime()
    {
        $xml = User::find(1)->toXml();

        $this->assertContains('<created-at type="datetime">2008-01-01T20:20:00+00:00</created-at>', $xml);
    }

    public function testShouldSerializeBoolean()
    {
        $xml = User::find(1)->toXml();

        $this->assertContains('<approved type="boolean">true</approved>', $xml);
    }


    /*##########################################################################
    # Nil Xml Serialization Test
    ##########################################################################*/

    public function testShouldSerializeNullString()
    {
        $user = new User(array('name' => null));
        $xml = $user->toXml();
        $this->assertContains('<name nil="true"></name>', $xml);
    }

    public function testShouldSerializeNullInteger()
    {
        $user = new User(array('id' => null));
        $xml = $user->toXml();

        $this->assertContains('<id type="integer" nil="true"></id>', $xml);
    }

    public function testShouldSerializeNullBinary()
    {
        $user = new UnitTest(array('blob_value' => null));
        $xml = $user->toXml();

        $this->assertContains('<blob-value encoding="base64" type="binary" nil="true"></blob-value>', $xml);
    }

    public function testShouldSerializeNullDate()
    {
        $user = new User(array('created_on' => null));
        $xml = $user->toXml();
        $this->assertContains('<created-on type="date" nil="true"></created-on>', $xml);

        $user = new User(array('created_on' => '0000-00-00'));
        $xml = $user->toXml();
        $this->assertContains('<created-on type="date" nil="true"></created-on>', $xml);
    }

    public function testShouldSerializeNullDatetime()
    {
        $user = new User(array('created_at' => null));
        $xml = $user->toXml();
        $this->assertContains('<created-at type="datetime" nil="true"></created-at>', $xml);

        $user = new User(array('created_at' => '0000-00-00 00:00:00'));
        $xml = $user->toXml();
        $this->assertContains('<created-at type="datetime" nil="true"></created-at>', $xml);
    }

    public function testShouldSerializeNullBoolean()
    {
        $user = new User(array('approved' => null));
        $xml = $user->toXml();

        $this->assertContains('<approved type="boolean" nil="true"></approved>', $xml);
    }


    /*##########################################################################
    # Database Connection Xml Serialization Test
    ##########################################################################*/

    public function testPassingHashShouldntReuseBuilder()
    {
        $options = array('include' => 'Comments');
        $mike = $this->users('mike');

        $firstXml  = $mike->toXml($options);
        $secondXml = $mike->toXml($options);
        
        $this->assertEquals($firstXml, $secondXml);
    }

    public function testIncludeUsesAssociationName()
    {
        $xml = $this->companies('maintainable')->toXml(array('include' => 'Users', 'indent' => 0));
        
        $this->assertContains('<users type="array">', $xml);
        $this->assertContains('<user>',               $xml);
        $this->assertContains('<user type="Client">', $xml);
    }

    public function testMethodsAreCalledOnObject()
    {
        $options = array('methods' => 'foo');
        $xmlRpc = $this->articles('xml_rpc');
        
        $xml = $xmlRpc->toXml($options);
        
        $this->assertContains('<foo>test serializer foo</foo>', $xml);
    }

    public function testShouldNotCallMethodsOnAssociationsThatDontRespond()
    {
        $xml = $this->companies('maintainable')->toXml(array('include' => 'Users', 
                                                             'indent'  => 2, 
                                                             'methods' => 'foo'));        

        $this->assertTrue(!method_exists($this->companies('maintainable')->users[0], 'foo'));
        $this->assertContains('  <foo>test serializer foo</foo>', $xml);
        $this->assertNotContains('    <foo>',                     $xml);
    }

    public function testShouldIncludeEmptyHasManyAsEmptyArray()
    {
        User::deleteAll();
        
        $xml = $this->companies('maintainable')->toXml(array('include' => 'Users', 'indent' => 2));

        $array = Mad_Support_ArrayObject::fromXml($xml);
        $this->assertEquals(array(), $array['company']['users']);
        
        $this->assertContains('<users type="array"></users>', $xml);
    }

    public function testShouldHasManyArrayElementsShouldIncludeTypeWhenDifferentFromGuessedValue()
    {
        $xml = $this->companies('maintainable')->toXml(array('include' => 'Employees', 
                                                             'indent'  => 2));

        $this->assertNotNull(Mad_Support_ArrayObject::fromXml($xml));
        $this->assertContains('<employees type="array">', $xml);
        $this->assertContains('<employee type="User">',   $xml);
        $this->assertContains('<employee type="Client">', $xml);
    }


    /*##########################################################################
    # Serialization Include tests
    ##########################################################################*/

    public function testSerializeWithoutIncludes()
    {
        $record  = $this->users('mike');
        $options = array('except' => array('updated_at', 'updated_on', 'first_name'));
        $serializer = new Mad_Model_Serializer_Xml($record, $options);
        
        $xml = $serializer->serialize($record, $options);

        $this->assertContains('<name>Mike Naberezny</name>', $xml);
        $this->assertNotContains('<updated-at',              $xml);
    }

    public function testSerializeIncludeSingleBelongsto()
    {
        $record  = $this->articles('xml_rpc');
        $options = array('include' => 'User');
        $serializer = new Mad_Model_Serializer_Xml($record, $options);

        $xml = $serializer->serialize($record, $options);
        
        $this->assertContains('<article>',                 $xml);
        $this->assertContains('<title>Easier XML-RPC for', $xml);
        $this->assertContains('<user>',                    $xml);
        $this->assertContains('<name>Mike Naberezny',      $xml);
    }

    public function testSerializeIncludeSingleHasMany()
    {
        $record  = $this->articles('xml_rpc');
        $options = array('include' => 'Comments');
        $serializer = new Mad_Model_Serializer_Xml($record, $options);

        $xml = $serializer->serialize($record, $options);

        $this->assertContains('<article>',                 $xml);
        $this->assertContains('<title>Easier XML-RPC for', $xml);
        $this->assertContains('<comments type="array">',   $xml);
        $this->assertContains('<comment>',                 $xml);
        $this->assertContains('<body>Comment A</body>',    $xml);
        $this->assertContains('<body>Comment B</body>',    $xml);
    }

    public function testSerializeIncludeMultiple()
    {
        $record  = $this->articles('xml_rpc');
        $options = array('include' => array('User', 'Comments'));
        $serializer = new Mad_Model_Serializer_Xml($record, $options);

        $xml = $serializer->serialize($record, $options);

        $this->assertContains('<article>',                 $xml);
        $this->assertContains('<title>Easier XML-RPC for', $xml);
        $this->assertContains('<user>',                    $xml);
        $this->assertContains('<name>Mike Naberezny',      $xml);
        $this->assertContains('<comments type="array">',   $xml);
        $this->assertContains('<comment>',                 $xml);
        $this->assertContains('<body>Comment A</body>',    $xml);
        $this->assertContains('<body>Comment B</body>',    $xml);
    }

    public function testSerializeIncludeWithOptions()
    {
        $record  = $this->articles('xml_rpc');
        $options = array('include' => array('User'     => array('only'   => 'name'), 
                                            'Comments' => array('except' => 'article_id')));
        $serializer = new Mad_Model_Serializer_Xml($record, $options);

        $xml = $serializer->serialize($record, $options);

        $this->assertContains('<article>',             $xml);
        $this->assertContains('<title>Easier XML-RPC', $xml);

        $this->assertContains('<user>',                $xml);
        $this->assertNotContains('<company_id>',       $xml);

        $this->assertContains('<comment>',             $xml);
        $this->assertNotContains('<article_id>',       $xml);
    }

    public function testSerializeWithMethods()
    {
        $record  = $this->articles('xml_rpc');
        $options = array('methods' => array('foo', 'intMethod', 'boolMethod'));
        $serializer = new Mad_Model_Serializer_Xml($record, $options);

        $xml = $serializer->serialize($record, $options);
        
        $this->assertContains('<foo>test serializer foo</foo>',                 $xml);
        $this->assertContains('<int-method type="integer">123</int-method>',    $xml);
        $this->assertContains('<bool-method type="boolean">true</bool-method>', $xml);        
    }


    /*##########################################################################
    # Model conversion Serialization Test
    ##########################################################################*/

    public function testToXml()
    {
        $record  = $this->users('mike');
        $options = array('include' => array('Comments' => array('only' => 'body')), 
                         'only'    => 'name');
    
        $xml = $record->toXml($options);

        $this->assertContains('<user>',                  $xml);
        $this->assertContains('<comments type="array">', $xml);
        $this->assertContains('<comment>',               $xml);
    }

    public function testFromXml()
    {
        $record = new Article;

        $xml = '<?xml version="1.0" encoding="UTF-8"?><article>'.
               '<id type="integer">1</id><title>Easier XML-RPC for PHP5</title>'.
               '<user-id type="integer">1</user-id></article>';
        $article = $record->fromXml($xml);

        $this->assertType('Article', $article);
        
        $this->assertEquals(1, $article->id);
        $this->assertEquals("Easier XML-RPC for PHP5", $article->title);
    }

    /*##########################################################################
    ##########################################################################*/

}