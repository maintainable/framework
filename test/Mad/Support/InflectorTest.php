<?php
/**
 * @category   Mad
 * @package    Mad_Support
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
 * @group      support
 * @category   Mad
 * @package    Mad_Support
 * @subpackage UnitTests
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Support_InflectorTest extends Mad_Test_Unit
{
    public function setUp()
    {
        Mad_Support_Inflector::clearCache();
    }

    // @todo better testing pluralizing words
    public function testPluralize()
    {
        $this->assertEquals('briefcases', Mad_Support_Inflector::pluralize('briefcase'));
        $this->assertEquals('categories', Mad_Support_Inflector::pluralize('category'));
    }

    // @todo better testing singularizing words
    public function testSingularize()
    {
        $this->assertEquals('briefcase', Mad_Support_Inflector::singularize('briefcases'));
        $this->assertEquals('category',  Mad_Support_Inflector::singularize('categories'));
    }

    // data given to camelize() MUST be underscored already
    public function testCamelize()
    {
        // most common scenarios (underscore => camelize)
        $this->assertEquals('Derek',           Mad_Support_Inflector::camelize('derek'));
        $this->assertEquals('DereksTest',      Mad_Support_Inflector::camelize('dereks_test'));
        $this->assertEquals('Dereks/Test',     Mad_Support_Inflector::camelize('dereks/test'));
        $this->assertEquals('DereksName/Test', Mad_Support_Inflector::camelize('dereks_name/test'));

        // not as common (already camelized)
        $this->assertEquals('Derek',        Mad_Support_Inflector::camelize('Derek'));
        $this->assertEquals('DereksTest',   Mad_Support_Inflector::camelize('dereksTest'));
        $this->assertEquals('DereksTest',   Mad_Support_Inflector::camelize('DereksTest'));
        $this->assertEquals('Dereks/Test',  Mad_Support_Inflector::camelize('Dereks_Test'));
    }

    // data given to camelize() MUST be underscored already
    public function testCamelizeLower()
    {
        // most common scenarios (underscore => camelize)
        $this->assertEquals('derek',           Mad_Support_Inflector::camelize('derek', 'lower'));
        $this->assertEquals('dereksTest',      Mad_Support_Inflector::camelize('dereks_test', 'lower'));
        $this->assertEquals('dereks/test',     Mad_Support_Inflector::camelize('dereks/test', 'lower'));
        $this->assertEquals('dereksName/test', Mad_Support_Inflector::camelize('dereks_name/test', 'lower'));

        // not as common (already camelized)
        $this->assertEquals('derek',        Mad_Support_Inflector::camelize('Derek', 'lower'));
        $this->assertEquals('dereksTest',   Mad_Support_Inflector::camelize('dereksTest', 'lower'));
        $this->assertEquals('dereksTest',   Mad_Support_Inflector::camelize('DereksTest', 'lower'));
        $this->assertEquals('dereks/test',  Mad_Support_Inflector::camelize('Dereks_Test', 'lower'));
    }

    public function testTitleize()
    {
        return true;
        $this->markTestSkipped();
    }

    // data given to underscore() MUST be camelized already
    public function testUnderscore()
    {
        // most common scenarios (camelize => underscore)
        $this->assertEquals('derek',            Mad_Support_Inflector::underscore('Derek'));
        $this->assertEquals('dereks_test',      Mad_Support_Inflector::underscore('dereksTest'));
        $this->assertEquals('dereks_test',      Mad_Support_Inflector::underscore('DereksTest'));
        $this->assertEquals('dereks_test',      Mad_Support_Inflector::underscore('Dereks_Test'));
        $this->assertEquals('dereks_name_test', Mad_Support_Inflector::underscore('DereksName_Test'));

        // not as common (already underscore)
        $this->assertEquals('derek',       Mad_Support_Inflector::underscore('derek'));
        $this->assertEquals('dereks_test', Mad_Support_Inflector::underscore('dereks_test'));
    }

    public function testDasherize()
    {
        $this->assertEquals('derek',            Mad_Support_Inflector::dasherize('Derek'));
        $this->assertEquals('dereks-test',      Mad_Support_Inflector::dasherize('dereksTest'));
        $this->assertEquals('dereks-test',      Mad_Support_Inflector::dasherize('DereksTest'));
        $this->assertEquals('dereks-test',      Mad_Support_Inflector::dasherize('Dereks_Test'));
        $this->assertEquals('dereks-name-test', Mad_Support_Inflector::dasherize('DereksName_Test'));
        $this->assertEquals('derek',            Mad_Support_Inflector::dasherize('derek'));
        $this->assertEquals('dereks-test',      Mad_Support_Inflector::dasherize('dereks_test'));
    }

    public function testHumanize()
    {
        // most common scenarios (column name => human)
        $this->assertEquals('Derek',          Mad_Support_Inflector::humanize('derek'));
        $this->assertEquals('Dereks test',    Mad_Support_Inflector::humanize('dereks_test'));
        $this->assertEquals('Dereks test',    Mad_Support_Inflector::humanize('dereks_test_id'));

        // not as common (columns are usually underscored)
        $this->assertEquals('Derek',          Mad_Support_Inflector::humanize('Derek'));
        $this->assertEquals('Dereks',         Mad_Support_Inflector::humanize('Dereks'));
        $this->assertEquals('Dereks test',    Mad_Support_Inflector::humanize('dereksTest'));
        $this->assertEquals('Dereks test',    Mad_Support_Inflector::humanize('dereksTestId'));
        $this->assertEquals('Dereks test',    Mad_Support_Inflector::humanize('DereksTest'));
        $this->assertEquals('Dereks test',    Mad_Support_Inflector::humanize('Dereks_Test'));
    }

    public function testDemodularize()
    {
        $this->assertEquals('Stuff', Mad_Support_Inflector::demodulize('Fax_Job_Stuff'));
        $this->assertEquals('Job',   Mad_Support_Inflector::demodulize('Fax_Job'));
        $this->assertEquals('Fax',   Mad_Support_Inflector::demodulize('Fax'));
    }

    // to table formatted string
    public function testTableize()
    {
        // most common scenarios (class => table)
        $this->assertEquals('dereks',       Mad_Support_Inflector::tableize('Derek'));
        $this->assertEquals('dereks',       Mad_Support_Inflector::tableize('Dereks'));
        $this->assertEquals('dereks_tests', Mad_Support_Inflector::tableize('dereksTest'));
        $this->assertEquals('dereks_tests', Mad_Support_Inflector::tableize('DereksTest'));
        $this->assertEquals('dereks_tests', Mad_Support_Inflector::tableize('Dereks_Test'));
        $this->assertEquals('dereks_tests', Mad_Support_Inflector::tableize('Dereks/Test'));

        // not as common (already underscore)
        $this->assertEquals('dereks',       Mad_Support_Inflector::tableize('derek'));
        $this->assertEquals('dereks_tests', Mad_Support_Inflector::tableize('dereks_test'));
        $this->assertEquals('dereks_tests', Mad_Support_Inflector::tableize('dereks/test'));
    }

    // to class formatted string
    public function testClassify()
    {
        $this->assertEquals('Derek',       Mad_Support_Inflector::classify('derek'));
        $this->assertEquals('DereksTest',  Mad_Support_Inflector::classify('dereks_test'));

        // not as common
        $this->assertEquals('Derek',       Mad_Support_Inflector::classify('Derek'));
        $this->assertEquals('Derek',       Mad_Support_Inflector::classify('Dereks'));
        $this->assertEquals('DereksTest',  Mad_Support_Inflector::classify('dereksTest'));
        $this->assertEquals('DereksTest',  Mad_Support_Inflector::classify('DereksTest'));
        $this->assertEquals('Dereks_Test', Mad_Support_Inflector::classify('Dereks_Test'));
    }

    public function testForeignKey()
    {
        return true;
        $this->markTestSkipped();
    }

    public function testOrdinalize()
    {
        return true;
        $this->markTestSkipped();
    }


    /*##########################################################################
    # Inflection Cache
    ##########################################################################*/

    // test setting inflection
    public function testSetCache()
    {
        Mad_Support_Inflector::setCache('documents', 'singularize', 'document');
        $this->assertEquals('document', Mad_Support_Inflector::getCache('documents', 'singularize'));
    }

    // test setting inflection
    public function testClearCache()
    {
        Mad_Support_Inflector::setCache('documents', 'singularize', 'document');
        Mad_Support_Inflector::clearCache();
        $this->assertEquals(false, Mad_Support_Inflector::getCache('documents', 'singularize'));
    }

    /*##########################################################################
    ##########################################################################*/

}

?>
