<?php
/**
 * @category   Mad
 * @package    Support
 * @subpackage UnitTests
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    Proprietary and Confidential 
 */

/**
 * Set environment
 */
if (!defined('MAD_ENV')) define('MAD_ENV', 'test');
if (!defined('MAD_ROOT')) {
    require_once dirname(dirname(dirname(dirname(__FILE__)))).'/config/environment.php';
}

/**
 * @group      support
 * @category   Mad
 * @package    Support
 * @subpackage UnitTests
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    Proprietary and Confidential
 */
class Mad_Support_ObjectTest extends Mad_Test_Unit
{
    public function testAttributeAccessorsAllowsReadAndWrite()
    {
        $book = Book::find('first');
        $this->assertEquals('1984', $book->title);

        $book->title = "1983";
        $this->assertEquals('1983', $book->title);
    }

    public function testAttributeReadersAllowRead()
    {
        $book = Book::find('first');
        $this->assertEquals(1, $book->id);
    }

    public function testAttributeReadersDontAllowWrite()
    {
        $book = Book::find('first');

        try {
            $book->id = 5;
        } catch (Exception $e) { return; }
        $this->fail('Expected exception wasn\'t raised');
    }

    public function testAttributeWriterAllowsWrite()
    {
        $book = Book::find('first');
        try {
            $book->hidden = 'test';
        } catch (Exception $e) { $this->fail('Unexepected exception raised'); }
    }

    public function testAttributeWriterDoesntAllowRead()
    {
        $book = Book::find('first');
        try {
            $book->hidden;
        } catch (Exception $e) { return; }
        $this->fail('Expected exception wasn\'t raised');
    }

    public function testAttributeAccessorUsesProxyGetterMethod()
    {
        $book = Book::find('first');
        $this->assertEquals('"empty"', $book->comments);
    }

    public function testAttributeAccessorUsesProxySetterMethod()
    {
        $book = Book::find('first');
        $book->comments = "<div>test</div>";
        $this->assertEquals('"test"', $book->comments);
    }

    public function testProxyGetterMethodsWorkWithUnderscoreAttributes()
    {
        $book = Book::find('first');
        $book->author_name = "    hey    ";
        $this->assertEquals('Hey', $book->author_name);
    }

    public function testProxyGetterMethodsWorkWithCamelAttributes()
    {
        $book = Book::find('first');
        $book->theAuthor = "    hey    ";
        $this->assertEquals('Hey', $book->theAuthor);
    }

    public function testAttributesWithNoPropertiesAssignAtRuntime()
    {
        $book = Book::find('first');
        $book->no_property = "test";
        $this->assertEquals('test', $book->no_property);
    }
}