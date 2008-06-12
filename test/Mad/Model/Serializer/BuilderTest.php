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
class Mad_Model_Serializer_BuilderTest extends Mad_Test_Unit
{
    // set up new db by inserting dummy data into the db
    public function setUp()
    {
        $this->fixtures('articles');
    }

    public function testInstruct()
    {
        $builder = new Mad_Model_Serializer_Builder;
        $builder->instruct();
        
        $expected = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $this->assertEquals($expected, $builder->__toString());
    }

    public function testTag()
    {
        $builder = new Mad_Model_Serializer_Builder;
        $builder->tag('div', 'my div & "escaped" <tag> value', array('id' => 'my_id'));

        $expected = '<div id="my_id">my div &amp; &quot;escaped&quot; &lt;tag&gt; value</div>';
        $this->assertEquals($expected, $builder->__toString());
    }
    
    public function testTagBlock()
    {
        $builder = new Mad_Model_Serializer_Builder;
        $tag = $builder->startTag('user', '');
            $tag->tag('age', 28, array('type' => 'integer'));
        $tag->end();

        $expected = '<user><age type="integer">28</age></user>';
        $this->assertEquals($expected, $builder->__toString());
    }

    public function testIndentation()
    {
        $builder = new Mad_Model_Serializer_Builder(array('indent' => 2));
        $builder->instruct();

        $tag = $builder->startTag('user', '');
            $tag->tag('age', 28, array('type' => 'integer'));
        $tag->end();

        $expected = <<< EOT
<?xml version="1.0" encoding="UTF-8"?>
<user>
  <age type="integer">28</age>
</user>

EOT;
        $this->assertEquals($expected, $builder->__toString());
    }
}