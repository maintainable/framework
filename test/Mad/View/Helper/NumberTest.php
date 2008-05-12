<?php
/**
 * @category   Mad
 * @package    Mad_View
 * @subpackage UnitTests
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    Proprietary and Confidential 
 */

/**
 * Set environment
 */
if (!defined('MAD_ENV')) define('MAD_ENV', 'test');
if (!defined('MAD_ROOT')) {
    require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config/environment.php';
}

/**
 * @group      view
 * @category   Mad
 * @package    Mad_View
 * @subpackage UnitTests
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    Proprietary and Confidential
 */
class Mad_View_Helper_NumberTest extends Mad_Test_Unit
{
    public function setUp()
    {
        $this->helper = new Mad_View_Helper_Number(new Mad_View_Base());
    }

    public function testNumberToPhone()
    {
        $this->assertEquals("800-555-1212", $this->helper->numberToPhone(8005551212));
        $this->assertEquals('(800) 555-1212', $this->helper->numberToPhone(8005551212,
                                                                array('areaCode' => true)));
        $this->assertEquals('800 555 1212', $this->helper->numberToPhone(8005551212,
                                                                array('delimiter' => ' ')));
        $this->assertEquals('(800) 555-1212 x 123', $this->helper->numberToPhone(8005551212,
                                                                array('areaCode' => true, 'extension' => 123)));
        $this->assertEquals('800-555-1212', $this->helper->numberToPhone(8005551212, 
                                                                array('extension' => '  ')));
        $this->assertEquals('800-555-1212', $this->helper->numberToPhone(8005551212));
        $this->assertEquals('+1-800-555-1212', $this->helper->numberToPhone(8005551212,
                                                                array('countryCode' => 1)));
        $this->assertEquals('+18005551212', $this->helper->numberToPhone(8005551212,
                                                                array('countryCode' => 1, 'delimiter' => '')));
        $this->assertEquals('22-555-1212', $this->helper->numberToPhone(225551212));
        $this->assertEquals('+45-22-555-1212', $this->helper->numberToPhone(225551212, 
                                                                array('countryCode' => 45)));
        $this->assertEquals('x', $this->helper->numberToPhone('x'));
        $this->assertEquals(null, $this->helper->numberToPhone(null));
    }
    
    public function testNumberToCurrency()
    {
       $this->assertEquals('$1,234,567,890.50', $this->helper->numberToCurrency(1234567890.50));
       $this->assertEquals("$1,234,567,890.51", $this->helper->numberToCurrency(1234567890.506));
       $this->assertEquals("$1,234,567,892", $this->helper->numberToCurrency(1234567891.50, array('precision' => 0)));
       $this->assertEquals("$1,234,567,890.5", $this->helper->numberToCurrency(1234567890.50, array('precision' => 1)));
       $this->assertEquals("&pound;1234567890,50", $this->helper->numberToCurrency(1234567890.50, 
                                                    array('unit' => "&pound;", 'separator' => ",", 'delimiter' => "")));
       $this->assertEquals("$1,234,567,890.50", $this->helper->numberToCurrency("1234567890.50"));

       $this->assertEquals('$x.', $this->helper->numberToCurrency('x'));
       $this->assertEquals(null, $this->helper->numberToCurrency(null));
    }

    public function testNumberToPercentage()
    {
        $this->assertEquals("100.000%", $this->helper->numberToPercentage(100));
        $this->assertEquals("100%", $this->helper->numberToPercentage(100, array('precision' => 0)));
        $this->assertEquals("302.06%", $this->helper->numberToPercentage(302.0574, array('precision' => 2)));
        $this->assertEquals("100.000%", $this->helper->numberToPercentage("100"));

        $this->assertEquals("x%", $this->helper->numberToPercentage("x"));
        $this->assertNull($this->helper->numberToPercentage(null));
    }

    public function testNumberWithDelimiter()
    {
        $this->assertEquals("12,345,678", $this->helper->numberWithDelimiter(12345678));
        $this->assertEquals("0", $this->helper->numberWithDelimiter(0));
        $this->assertEquals("123", $this->helper->numberWithDelimiter(123));
        $this->assertEquals("123,456", $this->helper->numberWithDelimiter(123456));
        $this->assertEquals("123,456.78", $this->helper->numberWithDelimiter(123456.78));
        $this->assertEquals("123,456.789", $this->helper->numberWithDelimiter(123456.789));
        $this->assertEquals("123,456.78901", $this->helper->numberWithDelimiter(123456.78901));
        $this->assertEquals("123,456,789.78901", $this->helper->numberWithDelimiter(123456789.78901));
        $this->assertEquals("0.78901", $this->helper->numberWithDelimiter(0.78901));
        $this->assertEquals("123,456.78", $this->helper->numberWithDelimiter("123456.78"));

        $this->assertEquals("x", $this->helper->numberWithDelimiter("x"));
        $this->assertNull($this->helper->numberWithDelimiter(null));
    }

    public function testNumberWithPrecision()
    {
        $this->assertEquals('111.235', $this->helper->numberWithPrecision(111.2346));
        $this->assertEquals('111.23', $this->helper->numberWithPrecision(111.2346, 2));
        $this->assertEquals('111.00', $this->helper->numberWithPrecision(111, 2));
        $this->assertEquals('111.235', $this->helper->numberWithPrecision('111.2346'));
        $this->assertEquals('112', $this->helper->numberWithPrecision('111.50', 0));
        $this->assertEquals('1234567892', $this->helper->numberWithPrecision(1234567891.50, 0));

        $this->assertEquals('x', $this->helper->numberWithPrecision('x'));
        $this->assertEquals(null, $this->helper->numberWithPrecision(null));
    }
    
    public function testNumberToHumanSize()
    {
        $this->assertEquals('0 Bytes', $this->helper->numberToHumanSize(0));
        $this->assertEquals('0 Bytes',   $this->helper->numberToHumanSize(0));
        $this->assertEquals('1 Byte',    $this->helper->numberToHumanSize(1));
        $this->assertEquals('3 Bytes',   $this->helper->numberToHumanSize(3.14159265));
        $this->assertEquals('123 Bytes', $this->helper->numberToHumanSize(123.0));
        $this->assertEquals('123 Bytes', $this->helper->numberToHumanSize(123));
        $this->assertEquals('1.2 KB',    $this->helper->numberToHumanSize(1234));
        $this->assertEquals('12.1 KB',   $this->helper->numberToHumanSize(12345));
        $this->assertEquals('1.2 MB',    $this->helper->numberToHumanSize(1234567));
        $this->assertEquals('1.1 GB',    $this->helper->numberToHumanSize(1234567890));
        $this->assertEquals('1.1 TB',    $this->helper->numberToHumanSize(1234567890123));
        $this->assertEquals('444 KB',    $this->helper->numberToHumanSize(444 * 1024));
        $this->assertEquals('1023 MB',   $this->helper->numberToHumanSize(1023 * 1048576));
        $this->assertEquals('3 TB',      $this->helper->numberToHumanSize(3 * 1099511627776));
        $this->assertEquals('1.18 MB',   $this->helper->numberToHumanSize(1234567, 2));
        $this->assertEquals('3 Bytes',   $this->helper->numberToHumanSize(3.14159265, 4));
        $this->assertEquals("123 Bytes", $this->helper->numberToHumanSize("123"));
        $this->assertNull($this->helper->numberToHumanSize('x'));
        $this->assertNull($this->helper->numberToHumanSize(null));
    }
}
