<?php
/**
 * @category   Mad
 * @package    Mad_Support
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
 * @group      support
 * @category   Mad
 * @package    Mad_Support
 * @subpackage UnitTests
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Support_FileUtilsTest extends Mad_Test_Unit
{
    public function setUp()
    {
        // remove test file
        @unlink(MAD_ROOT."/test/tmp/base_file.txt");
        Mad_Support_FileUtils::rm_rf(MAD_ROOT."/test/tmp/mad_file");
    }
    
    public function tearDown()
    {
        // remove test file
        @unlink(MAD_ROOT."/test/tmp/base_file.txt");
        Mad_Support_FileUtils::rm_rf(MAD_ROOT."/test/tmp/mad_file");
    }


    public function testCopyFileToFile()
    {
        Mad_Support_FileUtils::cp_r(MAD_ROOT."/test/fixtures/mad_file/base_file.txt", 
                               MAD_ROOT."/test/tmp/base_file.txt");
        $this->assertTrue(file_exists(MAD_ROOT."/test/tmp/base_file.txt"));
    }

    public function testCopyFileToDir()
    {
        Mad_Support_FileUtils::cp_r(MAD_ROOT."/test/fixtures/mad_file/base_file.txt", 
                               MAD_ROOT."/test/tmp/");
        $this->assertTrue(file_exists(MAD_ROOT."/test/tmp/base_file.txt"));
    }

    public function testCopyDirToDir()
    {
        Mad_Support_FileUtils::cp_r(MAD_ROOT."/test/fixtures/mad_file", 
                               MAD_ROOT."/test/tmp/");
        $this->assertTrue(file_exists(MAD_ROOT."/test/tmp/test_dir"));
        $this->assertTrue(file_exists(MAD_ROOT."/test/tmp/test_dir/sub_dir"));
        $this->assertTrue(file_exists(MAD_ROOT."/test/tmp/test_dir/sub_dir/sub_file.txt"));
    }


    public function testRecursiveRemove()
    {
        Mad_Support_FileUtils::cp_r(MAD_ROOT."/test/fixtures/mad_file", 
                               MAD_ROOT."/test/tmp/");
        $this->assertTrue(file_exists(MAD_ROOT."/test/tmp/test_dir"));

        Mad_Support_FileUtils::rm_rf(MAD_ROOT."/test/tmp/test_dir");
        $this->assertFalse(file_exists(MAD_ROOT."/test/tmp/test_dir"));
    }
}
