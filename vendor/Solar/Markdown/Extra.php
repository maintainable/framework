<?php
/**
 * 
 * Solar port of Markdown-Extra by Michel Fortin.
 * 
 * @category Solar
 * 
 * @package Solar_Markdown_Extra
 * 
 * @author Michel Fortin <http://www.michelf.com/projects/php-markdown/>
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Extra.php 2440 2007-04-21 14:33:44Z pmjones $
 * 
 */

/**
 * 
 * Solar port of Markdown-Extra by Michel Fortin.
 * 
 * This class implements a plugin set for the Markdown-Extra syntax;
 * be sure to visit the [Markdown-Extra][] site for syntax examples.
 * 
 * [Markdown-Extra]: http://www.michelf.com/projects/php-markdown/extra/
 * 
 * @category Solar
 * 
 * @package Solar_Markdown_Extra
 * 
 * @todo Implement the markdown-in-html portion of Markdown-Extra.
 * 
 */
class Solar_Markdown_Extra extends Solar_Markdown {
    
    /**
     * 
     * User-defined configuration values.
     * 
     * This sets the plugins and their processing order for the engine.
     * 
     * @var array
     * 
     */
    protected $_Solar_Markdown_Extra = array(
        'plugins' => array(
            
            // pre-processing on the source as a whole
            'Solar_Markdown_Plugin_Prefilter',
            'Solar_Markdown_Plugin_StripLinkDefs',
            
            // blocks
            'Solar_Markdown_Extra_Header',
            'Solar_Markdown_Extra_Table',
            'Solar_Markdown_Plugin_HorizRule',
            'Solar_Markdown_Plugin_List',
            'Solar_Markdown_Extra_DefList',
            'Solar_Markdown_Plugin_CodeBlock',
            'Solar_Markdown_Plugin_BlockQuote',
            'Solar_Markdown_Plugin_Html', //'Solar_Markdown_Extra_Html',
            'Solar_Markdown_Plugin_Paragraph',
            
            // spans
            'Solar_Markdown_Plugin_CodeSpan',
            'Solar_Markdown_Plugin_Image',
            'Solar_Markdown_Plugin_Link',
            'Solar_Markdown_Plugin_Uri',
            'Solar_Markdown_Plugin_Encode',
            'Solar_Markdown_Plugin_AmpsAngles',
            'Solar_Markdown_Extra_EmStrong',
            'Solar_Markdown_Plugin_Break',
        ),
    );
}
