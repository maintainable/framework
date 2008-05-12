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
class Mad_View_Helper_AssetTagTest extends Mad_Test_Unit
{
    public function setUp()
    {
        $this->view = new Mad_View_Base();
        $this->view->addHelper(new Mad_View_Helper_Tag($this->view));
        $this->view->addHelper(new Mad_View_Helper_AssetTag($this->view));
    }

    public function testImagePath()
    {
        $this->assertEquals("/images/xml",         $this->view->imagePath('xml'));
        $this->assertEquals("/images/xml.png",     $this->view->imagePath('xml.png'));
        $this->assertEquals("/images/dir/xml.png", $this->view->imagePath('dir/xml.png'));
        $this->assertEquals("/dir/xml.png",        $this->view->imagePath('/dir/xml.png'));
    }

    public function testImageTag()
    {
        $this->assertEquals('<img alt="Xml" src="/images/xml.png" />',                           $this->view->imageTag("xml.png"));
        $this->assertEquals('<img alt="rss syndication" src="/images/rss.gif" />',               $this->view->imageTag("rss.gif",   array("alt"  => "rss syndication")));
        $this->assertEquals('<img alt="Gold" height="70" src="/images/gold.png" width="45" />',  $this->view->imageTag("gold.png",  array("size" => "45x70")));
        $this->assertEquals('<img alt="Error" src="/images/error.png" />',                       $this->view->imageTag("error.png", array("size" => "45")));
        $this->assertEquals('<img alt="Error" src="/images/error.png" />',                       $this->view->imageTag("error.png", array("size" => "45 x 70")));
        $this->assertEquals('<img alt="Error" src="/images/error.png" />',                       $this->view->imageTag("error.png", array("size" => "x")));
        $this->assertEquals('<img alt="Logo" src="http://maintainable.com/images/logo.gif" />', $this->view->imageTag("http://maintainable.com/images/logo.gif"));
    }
}