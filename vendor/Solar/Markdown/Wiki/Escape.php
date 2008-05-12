<?php
/**
 * 
 * Span plugin to escape HTML remaining the span.
 * 
 * @category Solar
 * 
 * @package Solar_Markdown_Wiki
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Escape.php 2440 2007-04-21 14:33:44Z pmjones $
 * 
 */

/**
 * 
 * Span plugin to escape HTML remaining the span.
 * 
 * @category Solar
 * 
 * @package Solar_Markdown_Wiki
 * 
 */
class Solar_Markdown_Wiki_Escape extends Solar_Markdown_Plugin {
    
    /**
     * 
     * This is a span-level plugin.
     * 
     * @var bool
     * 
     */
    protected $_is_span = true;
    
    /**
     * 
     * Escapes HTML remaining in the text.
     * 
     * @param string $text The source text to be parsed.
     * 
     * @return string The transformed XHTML.
     * 
     */
    public function parse($text)
    {
        return $this->_escape($text);
    }
}
