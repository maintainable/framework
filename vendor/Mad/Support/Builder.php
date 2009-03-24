<?php
/**
 * @category   Mad
 * @package    Mad_Support
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * The base object from which all DataObjects are extended from
 *
 * @category   Mad
 * @package    Mad_Support
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Support_Builder
{
    protected $_xml = null;

    /**
     * @param   array   $options
     */
    public function __construct($options = array())
    {
        $options['indent'] = isset($options['indent']) ? $options['indent'] : 0;
        $this->_options = $options;

        $this->_xml = new XmlWriter();
        $this->_xml->openMemory();

        if (!empty($options['indent'])) {
            $this->_xml->setIndent(true);
            $this->_xml->setIndentString(str_repeat(' ', $options['indent']));
        }
    }

    /**
     * Flush XML output
     */
    public function __toString()
    {
        $output = $this->_xml->flush(false);

        // fix root indentation
        if (!empty($this->_options['indent'])) {
            $indent = str_repeat(' ', $this->_options['indent']);
            $output = preg_replace("/>($indent+)</", ">\n$1<", $output);
        }

        return $output;
    }

    /**
     * Insert a processing instruction into the XML markup. E.g.
     */
    public function instruct($version = '1.0', $encoding = 'UTF-8')
    {
        $this->_xml->startDocument($version, $encoding);
    }

    /** 
     * Start/end entire tag at once
     */
    public function tag($name, $value = '', $attributes = array())
    {
        $this->startTag($name, $value, $attributes);
        $this->end();
    }

    /**
     * Start a new tag specifying element/value/attributes
     * 
     * @param   string  $name
     * @param   string  $value
     * @param   array   $attributes
     */
    public function startTag($name, $value = '', $attributes = array())
    {
        $this->_xml->startElement($name);
        foreach ($attributes as $attrKey => $attrValue) {
            $this->_xml->writeAttribute($attrKey, $attrValue);
        }
        $this->_xml->text($value);
        return $this;
    }
    
    /** 
     * Finish an element started by <code>startTag()</code>
     */
    public function end()
    {
        $this->_xml->endElement();
    }
}
