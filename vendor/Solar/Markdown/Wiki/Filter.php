<?php
/**
 * 
 * Escapes all remaining HTML, and replaces HTML tokens.
 * 
 * @category Solar
 * 
 * @package Solar_Markdown_Wiki
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Filter.php 2440 2007-04-21 14:33:44Z pmjones $
 * 
 */

/**
 * 
 * Escapes all remaining HTML, and replaces HTML tokens.
 * 
 * @category Solar
 * 
 * @package Solar_Markdown_Wiki
 * 
 */
class Solar_Markdown_Wiki_Filter extends Solar_Markdown_Plugin_Prefilter {
    
    /**
     * 
     * Runs during the cleanup() phase.
     * 
     * @var bool
     * 
     */
    protected $_is_cleanup = true;
    
    /**
     * 
     * Cleans up source text after processing.
     * 
     * @param string $text The source text.
     * 
     * @return string The text after cleaning it up.
     * 
     */
    public function cleanup($text)
    {
        // all HTML remaining in the text should be escaped
        $text = $this->_escape($text);
        
        // render all html tokens back into the text
        return $this->_unHtmlToken($text);
    }
}
