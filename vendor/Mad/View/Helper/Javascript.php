<?php
/**
 * @category   Mad
 * @package    Mad_View
 * @subpackage Helper
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * @category   Mad
 * @package    Mad_View
 * @subpackage Helper
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_View_Helper_Javascript extends Mad_View_Helper_Javascript_Base
{
    protected $_json = null;

    public function escapeJavascript($javascript)
    {
        $escaped = str_replace(array('\\',   "\r\n", "\r",  "\n",  '"',  "'"), 
                               array('\0\0', "\\n",  "\\n", "\\n", '\"', "\'"), 
                               $javascript);
        return $escaped;        
    }

    public function jsonEncode($data)
    {
        if ($this->_json === null) {
            $this->_json = new Solar_Json(array());
        }
        return $this->_json->encode($data);
    }
    
    public function javascriptTag($content, $htmlOptions = array())
    {
        return $this->contentTag('script', 
                                 $this->javascriptCdataSection($content),
                                 array_merge($htmlOptions, array('type' => 'text/javascript')));
    }
    
    // @todo nodoc
    public function javascriptCdataSection($content)
    {
        return "\n//" . $this->cdataSection("\n$content\n//") . "\n";
    }
}
