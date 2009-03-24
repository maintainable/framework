<?php
/**
 * @category   Mad
 * @package    Mad_Test
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * Builds test suites that can be executed by PHPUnit.
 *
 * @category   Mad
 * @package    Mad_Test
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Test_SuiteBuilder
{   
    /**
     * Test directories, relative to MAD_ROOT
     *
     * @param array<string> 
     */
    protected $_dirs = array('test/unit', 'test/functional');
    
    /**
     * Temp file with test inclusion list, relative to MAD_ROOT
     *
     * @param string
     */
    protected $_tmpfile = 'tmp/test_pathnames.txt';


    /**
     * Build a test suite.  If a test inclusion file exists, only
     * the tests specified in that file will be included and then 
     * the file itself will be destroyed.  Otherwise, all test files 
     * found for this project will be included.
     *
     * @return PHPUnit_Framework_TestSuite
     */
    public function build($name)
    {
        $suite = new PHPUnit_Framework_TestSuite($name);

        $tmpfile = $this->getListPathname();
        if (file_exists($tmpfile)) {
            $pathnames = $this->readFileList($tmpfile);
            unlink($tmpfile);
        } else {
            $pathnames = $this->findAll();
        }

        foreach ($pathnames as $pathname) { 
            require_once $pathname;
            $suite->addTestSuite( $this->pathToClass($pathname) );
        }

        return $suite;
    }

    /**
     * Path to temporary file containing test pathnames.
     *
     * @return string
     */
    public function getListPathname()
    {
        return MAD_ROOT . DIRECTORY_SEPARATOR . 
               str_replace('/', DIRECTORY_SEPARATOR, $this->_tmpfile);
    }

    /**
     * Find all test files in this project and return their pathnames.
     *
     * @return array<string>  Pathnames
     */
    public function findAll()
    {
        $pathnames = array();
        foreach ($this->getTestDirectories() as $dir) {
            foreach(new RecursiveIteratorIterator(
                     new RecursiveDirectoryIterator($dir)) as $file) {

                if ($file->isFile() && preg_match('/Test.php$/', $file->getFilename())) {
                    $pathnames[] = $file->getPathname();
                }
            }
        }
        return $pathnames;
    }

    /**
     * Read $filename containing pathnames.  Verify and return them.
     * 
     * @param  string  $filename  Filename to read
     * @return array<string>      Valid pathnames read from the file
     */
    public function readFileList($filename)
    {
        $pathnames = array();
        foreach (file($filename) as $line) {
            $pathname = rtrim($line);
            if (!empty($pathname) && file_exists($pathname)) {
                $pathnames[] = $pathname;
            }
        }
        return $pathnames;
    }

    /**
     * Get an array of all test directories for this project
     * 
     * @return array<string>  Directories
     */
    public function getTestDirectories() 
    {
        $dirs = array();
        foreach ($this->_dirs as $type) {
            $dirs[] = MAD_ROOT . DIRECTORY_SEPARATOR
                    . str_replace('/', DIRECTORY_SEPARATOR, $type) 
                    . DIRECTORY_SEPARATOR;        
        }
        return $dirs;
    }

    /**
     * Given a $pathname, return the PHP test class name contained within it.
     *
     * @param  string  $pathname  Path to test file
     * @return string             Class name
     */
    public function pathToClass($pathname)
    {
        foreach ($this->getTestDirectories() as $dir) {
            $quoted = preg_quote($dir, '/');
            $replaced = preg_replace("/^{$quoted}(.*)\.php/", '\\1', $pathname);
            if ($replaced != $pathname) {
                return str_replace(DIRECTORY_SEPARATOR, '_', $replaced);
            }
        }
    }

}