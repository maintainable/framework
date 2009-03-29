<?php
/**
 * @category   Mad
 * @package    Mad_Model
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
 * @group      model
 * @category   Mad
 * @package    Mad_Model
 * @subpackage UnitTests
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Model_PerformanceTest extends Mad_Test_Unit
{
    // debug/test out some performance stuff... 
    // no need to actually run this as part of the suite. 
    public function testPerf()
    {
        /*
        $t = new Horde_Support_Timer;
        
        $this->fixtures('articles', 'users', 'comments', 'categories', 'tags', 'taggings');
        $a = Article::find(1);

        // cloning objects
        $t->push();
        for ($i = 0; $i < 10000; $i++) {
            $obj = clone $a;
            foreach ($a->comments as $c) {
                // nada
            }
        }
        print 'clone: '.$t->pop()."\n";
        
        // new objects
        $t->push();
        for ($i = 0; $i < 10000; $i++) {
            $obj = new Article;
        }
        print 'new: '.$t->pop()."\n";
        */
    }
}