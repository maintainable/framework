<?php
/**
 * @category   Mad
 * @package    Mad_Controller
 * @subpackage UnitTests
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * Set environment
 */
if (!defined('MAD_ENV')) define('MAD_ENV', 'test');
if (!defined('MAD_ROOT')) {
    require_once dirname(dirname(dirname(dirname(__FILE__)))).'/config/environment.php';
}

/**
 * Used for functional testing of controller classes
 *
 * @group      controller
 * @category   Mad
 * @package    Mad_Controller
 * @subpackage UnitTests
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Controller_FileUploadTest extends Mad_Test_Functional
{
    public function testFileUpload()
    {
        $options = array('name'     => 'foo', 
                         'size'     => 'baz',
                         'type'     => 'bar', 
                         'tmp_name' => 'bop');
        $upload = new Mad_Controller_FileUpload($options);

        $this->assertEquals('foo', $upload->originalFilename);
        $this->assertEquals('baz', $upload->length);
        $this->assertEquals('bar', $upload->contentType);
        $this->assertEquals('bop', $upload->path);
    }
}