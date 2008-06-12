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
        $this->fixtures('users', 'articles', 'comments');
    }

    public function testSerializeWithoutIncludes()
    {
        $record  = $this->users('mike');
        $options = array('except' => array('updated_at', 'updated_on', 'first_name'));
        $serializer = new Mad_Model_Serializer_Xml($record, $options);
        
        $expected = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<user>
  <approved type="boolean">1</approved>
  <created-at type="datetime">2008-01-01 12:20:00</created-at>
  <created-on type="date">2008-01-01</created-on>
  <id type="integer">1</id>
  <name>Mike Naberezny</name>
</user>

XML;
        $this->assertEquals($expected, $serializer->serialize());
    }

    public function testSerializeIncludeSingleBelongsto()
    {
        $record  = $this->articles('xml_rpc');
        $options = array('include' => 'User');
        $serializer = new Mad_Model_Serializer_Xml($record, $options);

        $expected = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<article>
  <id type="integer">1</id>
  <title>Easier XML-RPC for PHP5</title>
  <user-id type="integer">1</user-id>
  <user>
    <approved type="boolean">1</approved>
    <created-at type="datetime">2008-01-01 12:20:00</created-at>
    <created-on type="date">2008-01-01</created-on>
    <first-name>Mike</first-name>
    <id type="integer">1</id>
    <name>Mike Naberezny</name>
    <updated-at type="datetime">2008-01-01 12:20:00</updated-at>
    <updated-on type="date">2008-01-01</updated-on>
  </user>
</article>

XML;
        $this->assertEquals($expected, $serializer->serialize());
    }

    public function testSerializeIncludeSingleHasMany()
    {
        $record  = $this->articles('xml_rpc');
        $options = array('include' => 'Comments');
        $serializer = new Mad_Model_Serializer_Xml($record, $options);

        $expected = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<article>
  <id type="integer">1</id>
  <title>Easier XML-RPC for PHP5</title>
  <user-id type="integer">1</user-id>
  <comments type="array">
    <comment>
      <article-id type="integer">1</article-id>
      <body>Comment A</body>
      <id type="integer">1</id>
      <user-id type="integer">1</user-id>
    </comment>
    <comment>
      <article-id type="integer">1</article-id>
      <body>Comment B</body>
      <id type="integer">2</id>
      <user-id type="integer">1</user-id>
    </comment>
  </comments>
</article>

XML;

        $this->assertEquals($expected, $serializer->serialize());
    }

    public function testSerializeIncludeMultiple()
    {
        $record  = $this->articles('xml_rpc');
        $options = array('include' => array('User', 'Comments'));
        $serializer = new Mad_Model_Serializer_Xml($record, $options);

        $expected = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<article>
  <id type="integer">1</id>
  <title>Easier XML-RPC for PHP5</title>
  <user-id type="integer">1</user-id>
  <user>
    <approved type="boolean">1</approved>
    <created-at type="datetime">2008-01-01 12:20:00</created-at>
    <created-on type="date">2008-01-01</created-on>
    <first-name>Mike</first-name>
    <id type="integer">1</id>
    <name>Mike Naberezny</name>
    <updated-at type="datetime">2008-01-01 12:20:00</updated-at>
    <updated-on type="date">2008-01-01</updated-on>
  </user>
  <comments type="array">
    <comment>
      <article-id type="integer">1</article-id>
      <body>Comment A</body>
      <id type="integer">1</id>
      <user-id type="integer">1</user-id>
    </comment>
    <comment>
      <article-id type="integer">1</article-id>
      <body>Comment B</body>
      <id type="integer">2</id>
      <user-id type="integer">1</user-id>
    </comment>
  </comments>
</article>

XML;
        $this->assertEquals($expected, $serializer->serialize());
    }

    public function testSerializeIncludeWithOptions()
    {
        $record  = $this->articles('xml_rpc');
        $options = array('include' => array('User'     => array('only'   => 'name'), 
                                            'Comments' => array('except' => 'article_id')));
        $serializer = new Mad_Model_Serializer_Xml($record, $options);

        $expected = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<article>
  <id type="integer">1</id>
  <title>Easier XML-RPC for PHP5</title>
  <user-id type="integer">1</user-id>
  <user>
    <name>Mike Naberezny</name>
  </user>
  <comments type="array">
    <comment>
      <body>Comment A</body>
      <id type="integer">1</id>
      <user-id type="integer">1</user-id>
    </comment>
    <comment>
      <body>Comment B</body>
      <id type="integer">2</id>
      <user-id type="integer">1</user-id>
    </comment>
  </comments>
</article>

XML;
        $this->assertEquals($expected, $serializer->serialize());
    }

    public function testSerializeWithMethods()
    {
        $record  = $this->articles('xml_rpc');
        $options = array('methods' => array('foo', 'intMethod', 'boolMethod'));

        $serializer = new Mad_Model_Serializer_Xml($record, $options);

        $expected = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<article>
  <id type="integer">1</id>
  <title>Easier XML-RPC for PHP5</title>
  <user-id type="integer">1</user-id>
  <foo>test serializer foo</foo>
  <int-method type="integer">123</int-method>
  <bool-method type="boolean">1</bool-method>
</article>

XML;
        $this->assertEquals($expected, $serializer->serialize());
    }


    // Model conversion

    public function testToXml()
    {
        $record  = $this->users('mike');
        $options = array('include' => array('Comments' => array('only' => 'body')), 
                         'only'    => 'name');
                     
        $expected = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<user>
  <name>Mike Naberezny</name>
  <comments type="array">
    <comment>
      <body>Comment A</body>
    </comment>
    <comment>
      <body>Comment B</body>
    </comment>
  </comments>
</user>

XML;
        $this->assertEquals($expected, $record->toXml($options));
    }

    public function testFromXml()
    {
        $record = new Article;
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?><article><id type="integer">1</id><title>Easier XML-RPC for PHP5</title><user-id type="integer">1</user-id></article>';
        $article = $record->fromXml($xml);

        $this->assertType('Article', $article);
        
        $this->assertEquals(1, $article->id);
        $this->assertEquals("Easier XML-RPC for PHP5", $article->title);
    }
}