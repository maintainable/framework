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
    require_once dirname(dirname(dirname(dirname(__FILE__)))).'/config/environment.php';
}

/**
 * @group      model
 * @category   Mad
 * @package    Mad_Model
 * @subpackage UnitTests
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_Model_PerformanceTest extends Mad_Test_Unit
{
    // debug/test out some performance stuff... 
    // no need to actually run this as part of the suite. 
    public function testPerf()
    {
        /*
        $t = new Mad_Support_Timer;
        
        $this->fixtures('articles', 'users', 'comments', 'categories', 'tags', 'taggings');
        $a = Article::find(1);

        // cloning objects
        $t->start();
        for ($i = 0; $i < 10000; $i++) {
            $obj = clone $a;
            foreach ($a->comments as $c) {
                // nada
            }
        }
        print 'clone: '.$t->finish()."\n";
        
        // new objects
        $t->start();
        for ($i = 0; $i < 10000; $i++) {
            $obj = new Article;
        }
        print 'new: '.$t->finish()."\n";
        */
    }
}