<?php
/**
 * @category   Mad
 * @package    Mad_Controller
 * @subpackage UnitTests
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
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
 * @group      controller
 * @category   Mad
 * @package    Mad_Controller
 * @subpackage UnitTests
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Controller_ScannerTest extends Mad_Test_Unit
{
    public function testConstructorAnalyzesRoutes()
    {
        $mapper = new Horde_Routes_Mapper();
        $mapper->connect(':controller/:action/:id');

        $scanner = new Mad_Controller_Scanner($mapper);
        $this->assertTrue(is_callable($scanner->getCallback()));
    }
    
    public function testAnalyzeChoosesFilesystemStrategyWhenAnyControllerIsNotKnown()
    {
        $mapper = new Horde_Routes_Mapper();
        $mapper->resource('book', 'books');
        $mapper->resource('author', 'authors');
        $mapper->connect(':controller/:action/:id');
        
        $scanner = new Mad_Controller_Scanner($mapper);
        $scanner->analyze();
    
        $this->assertEquals(array($scanner, 'scanFilesystem'), 
                            $scanner->getCallback());
    }
    
    public function testAnalyzeChoosesHardcodesStrategyWhenAnyControllerIsNotKnown()
    {
        $mapper = new Horde_Routes_Mapper();
        $mapper->resource('book', 'books');
        $mapper->resource('author', 'authors');
        
        $scanner = new Mad_Controller_Scanner($mapper);
        $scanner->analyze();
    
        $this->assertEquals(array($scanner, 'scanHardcodes'), 
                            $scanner->getCallback());
    }
    
    public function testScanHardcodes()
    {
        $mapper = new Horde_Routes_Mapper();
        $mapper->resource('author', 'authors');
        $mapper->resource('book', 'books');
    
        $scanner = new Mad_Controller_Scanner($mapper);
        $controllers = $scanner->scanHardCodes();
    
        sort($controllers);
        $this->assertEquals(array('authors', 'books'), 
                            $controllers);
    }

    public function testScanFilesystem()
    {
        $mapper = new Horde_Routes_Mapper();
        $mapper->connect(':controller/:action/:id');
    
        $scanner = new Mad_Controller_Scanner($mapper);
        $controllers = $scanner->scanFilesystem(MAD_ROOT . '/app/controllers');
    
        sort($controllers);
        $this->assertEquals(array('application', 'error', 'unit_test'), 
                            $controllers);
    }

}