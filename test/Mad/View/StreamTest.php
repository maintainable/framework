<?php
/**
 * @category   Mad
 * @package    Mad_View
 * @subpackage UnitTests
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt 
 */

/**
 * Set environment
 */
if (!defined('MAD_ENV')) define('MAD_ENV', 'test');
if (!defined('MAD_ROOT')) {
    require_once dirname(dirname(dirname(dirname(__FILE__)))).'/config/environment.php';
}

/**
 * @category   Mad
 * @package    Mad_View
 * @subpackage UnitTests
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_View_StreamTest extends Mad_Test_Unit
{
    public function setUp()
    {
        if (! in_array('view', stream_get_wrappers())) {
            stream_wrapper_register('view', 'Mad_View_Stream');
        }
    }


    public function testRewritingAtDollarToHtmlEntities()
    {
        $expected = 'htmlentities($foo, ENT_QUOTES, \'utf-8\')';
        $actual   = $this->process('@$foo');
        
        $this->assertEquals($expected, $actual);
    }

    public function testRewritingAtDollarToHtmlEntitiesUsingArrayKey()
    {
        $expected = 'htmlentities($this->foo[\'test\'], ENT_QUOTES, \'utf-8\')';
        $actual   = $this->process('@$this->foo[\'test\']');
        
        $this->assertEquals($expected, $actual);
    }

    public function testRewritingSquareBracketsToArray()
    {
        $result = array('controller' => 'home', 'action' => 'index');
        $code   = $this->process('["controller" => "home", "action" => "index"];');
        $this->assertEvalsToSame($result, $code);
    }

    public function testRewritingSquareBracketsToArrayWithTrailingComma()
    {
        $result = array('controller' => 'home', 'action' => 'index', );
        $code   = $this->process('["controller" => "home", "action" => "index", ];');
        $this->assertEvalsToSame($result, $code);
    }

    public function testRewritingSquareBracketsToArrayWithNoWhitespace()
    {
        $result = array('controller' => 'home', 'action' => 'index');
        $code   = $this->process('["controller"=>"home","action"=>"index"];');
        $this->assertEvalsToSame($result, $code);        
    }

    public function testRewritingSquareBracketsToArrayWithExtraWhitespace()
    {
        $result = array('controller' => 'home', 'action' => 'index');
        $code   = $this->process("['controller'\r\n\t=>'home',\n\n\n\t  'action'\n=>\r'index'\t];");
        $this->assertEvalsToSame($result, $code);        
    }

    public function testRewritingSquareBracketsToArrayNested()
    {
        $result = array('baz' => array('quux' => 'zot'));
        $code   = $this->process('["baz" => ["quux" => "zot"]];');
        $this->assertEvalsToSame($result, $code);
    }

    public function testRewritingSquareBracketsToArrayAdjacent()
    {
        $result = array('foo' => 'bar');
        $code   = $this->process('$a = ["foo" => "bar"]; $b = ["foo" => "bar"];');
        $this->assertEvalsToSame($result, $code);
    }

    public function testRewritingPHPShortTags()
    {
        $result = '<?php $a = 5; ?>';
        $code   = $this->process('<? $a = 5; ?>');
        $this->assertSame($result, $code);
    }
    
    public function testRewritingPHPShortTagsMultiple()
    {
        $result = '<?php $a = 5; ?><?php $b = 7; ?>';
        $code   = $this->process('<? $a = 5; ?><? $b = 7; ?>');
        $this->assertSame($result, $code);
    }

    public function testRewritingPHPShortEchoTags()
    {
        $result = '<?php echo 5; ?>';
        $code   = $this->process('<?= 5; ?>');
        $this->assertSame($result, $code);
    }
    
    public function testRewritingPHPShortEchoTagsMultiple()
    {
        $result = '<?php echo 5; ?><?php echo 7; ?>';
        $code   = $this->process('<?= 5; ?><?= 7; ?>');
        $this->assertSame($result, $code);
    }
    
    
    /**
     * Process view data through the stream wrapper and return
     * the results.  $data is what would be in the view template.
     *
     * @param  string $data  Input data
     * @return string        Data returned from stream wrapper
     */
    public function process($data)
    {
        $tmpnam = tempnam("/tmp", "view");
        file_put_contents($tmpnam, $data);

        $stream = fopen("view://$tmpnam", 'r');

        $meta = stream_get_meta_data($stream);       
        $obj = $meta['wrapper_data'];
        $obj->forceShortTagRewrite = true;
        
        $contents = stream_get_contents($stream);
        fclose($stream);

        return $contents;
    }
    
    
    /**
     * Assert that PHP code in a string evaluates 
     * the same to an expected value.  Value returned from eval()
     * cannot be FALSE (PHP limitation).
     *
     * @param  mixed  $result   Expected result
     * @param  string $code     PHP code to evaluate
     * @return void
     */
    public function assertEvalsToSame($result, $code)
    {
        $actual = eval("return $code");

        if ($actual === false) {
            $this->fail("PHP code evaluation failed:\n$code");
        }

        $this->assertSame($result, $actual);
    }
}
