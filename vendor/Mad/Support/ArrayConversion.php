<?php
/**
 * @category   Mad
 * @package    Mad_Support
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */

/**
 * The base object from which all DataObjects are extended from
 *
 * @category   Mad
 * @package    Mad_Support
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_Support_ArrayConversion
{
    public $xmlTypeNames = array(
      "integer" => "integer", 
      "double"  => "float", 
      "boolean" => "boolean"
    );

    public $xmlFormatting = array(
      "boolean"  => 'formatBoolean',
      "binary"   => 'formatBinary',
      "date"     => 'formatDate',
      "datetime" => 'formatDatetime',
      "yaml"     => 'formatYaml'
    );
    
    public $xmlParsing = array(
      "symbol"       => 'parseSymbol',
      "date"         => 'parseDate',
      "datetime"     => 'parseDatetime',
      "dateTime"     => 'parseDatetime',
      "integer"      => 'parseInteger',
      "float"        => 'parseFloat',
      "double"       => 'parseDouble',
      "decimal"      => 'parseDecimal',
      "boolean"      => 'parseBoolean',
      "string"       => 'parseString',
      "yaml"         => 'parseYaml',
      "base64Binary" => 'parseBase64Binary',
      "file"         => 'parseFile',
    );
    
    /**
     * Convert an array to XML. While PHP has a single array() to do 
     * ordered and associative collections, we've split toXml into two
     * separate methods to resemble the Rails code more closely
     * 
     * @param   array   $array
     * @param   array   $options
     */
    public function toXml($array, $options = array()) 
    {
        // associative
        if (!empty($array) && !$array instanceof Mad_Model_Collection && !is_int(key($array))) {
            return $this->hashToXml($array, $options);

        // numeric
        } else {
            return $this->arrayToXml($array, $options);
        }
    }


    /**
     * Convert an XML string to an associative array
     * 
     * @param   string  $xmlStr
     * @return  array
     */
    public function fromXml($xmlStr)
    {
        // build array data struct from the xml
        $xml = new SimpleXMLElement($xmlStr);
        $params = $this->_parseElement($xml);

        // remove dasherized keys and typecast values
        $params = $this->_undasherizeKeys($params);
        return $this->_typecastXmlValue($params);
    }

    /**
     * Convert an associative array to XML
     * 
     * @todo - complete this!
     * @param   array   $options
     * @return  string
     */
    public function hashToXml($hash, $options = array())
    {  
        if (!isset($options['indent'])) { $options['indent'] = 2; }
        if (!isset($options['root']))   { $options['root']   = 'hash'; }

        if (empty($options['builder'])) {
            $options['builder'] = new Mad_Support_Builder(
                array('indent' => $options['indent']));
        }
        if (empty($options['skipInstruct'])) {
            $options['builder']->instruct(); 
        }
        $dasherize = !array_key_exists('dasherize', $options) || !empty($options['dasherize']);
        $root = $dasherize ? Mad_Support_Inflector::dasherize($options['root']) : $options['root'];

        $tag = $options['builder']->startTag($root); 
        foreach ($hash as $key => $value) {
            // associative array
            if (is_array($value) && !is_int(key($value))) {
                $opts = array_merge($options, array('root' => $key, 'skipInstruct' => true));
                $this->hashToXml($value, $opts);

            // array
            } elseif (is_array($value)) {
                $opts = array_merge($options, array('children'     => Mad_Support_Inflector::singularize($key), 
                                                    'root'         => $key, 
                                                    'skipInstruct' => true));
                $this->arrayToXml($value, $opts);

            } else {
                // object
                if (is_object($value) && is_callable(array($value, 'toXml'))) {
                    $opts = array_merge($options, array('root' => $key, 'skipInstruct' => true));
                    $value->toXml($opts);

                // object without toXml
                } elseif (is_object($value)) {
                    throw new Mad_Support_Exception("Not all elements respond to toXml");

                // native type
                } else {
                    if (isset($this->xmlTypeNames[gettype($value)])) {
                        $typeName = $this->xmlTypeNames[gettype($value)];
                    } else {
                        $typeName = null;
                    }
                    $key = $dasherize ? Mad_Support_Inflector::dasherize($key) : $key;
                    
                    if (!empty($options['skipTypes']) || $value === null || $typeName === null) {
                        $attributes = array();
                    } else {
                        $attributes = array('type' => $typeName);
                    }

                    if ($value === null) { $attributes['nil'] = 'true'; }

                    if (isset($this->xmlFormatting[$typeName])) {
                        $formatter = $this->xmlFormatting[$typeName];
                        $value = $value ? $this->{$formatter}($value) : null;
                    }

                    $options['builder']->tag($key, $value, $attributes);
                }
            }
            
        }
        $tag->end();
        return $options['builder']->__toString();
    }

    /**
     * Convert array collection to XML
     * @param   array   $array
     * @param   array   $options
     */
    public function arrayToXml($array, $options = array())
    {
        $firstElt  = current($array);
        $firstType = is_object($firstElt) ? get_class($firstElt) : gettype($firstElt);
        $sameTypes = true;

        foreach ($array as $element) {
            // either an array or object with toXml method
            if (!is_array($element) && !is_callable(array($element, 'toXml'))) {
                throw new Mad_Support_Exception("Not all elements respond to toXml");
            }
            if (get_class($element) != $firstType) { $sameTypes = false; }
        }

        if (!isset($options['root'])) {
            if ($sameTypes && count($array) > 0) {
                $options['root'] = Mad_Support_Inflector::pluralize($firstType);
            } else {
                $options['root'] = 'records';
            }
        }
        if (!isset($options['children'])) {
            $options['children'] = Mad_Support_Inflector::singularize($options['root']);
        }

        if (!isset($options['indent']))    { $options['indent']    = 2; }
        if (!isset($options['skipTypes'])) { $options['skipTypes'] = false; }

        if (empty($options['builder'])) {
            $options['builder'] = new Mad_Support_Builder(
                array('indent' => $options['indent']));
        }

        $root = $options['root'];
        unset($options['root']);
        
        $children = $options['children'];
        unset($options['children']);

        if (!array_key_exists('dasherize', $options) || !empty($options['dasherize'])) {
            $root = Mad_Support_Inflector::dasherize($root);
        }
        if (empty($options['skipInstruct'])) {
            $options['builder']->instruct(); 
        }

        $opts = array_merge($options, array('root' => $children));

        $builder = $options['builder'];
        $attrs   = $options['skipTypes'] ? array() : array('type' => 'array');
        
        // no elements in array
        if (count($array) == 0) {
            $builder->tag($root, '', $attrs);
            
        // build xml from elements
        } else {
            $tag = $builder->startTag($root, '', $attrs);
                $opts['skipInstruct'] = true;
                foreach ($array as $element) {
                    // associative array
                    if (is_array($element) && !is_int(key($element))) {
                        $this->hashToXml($element, $opts);
                    // array
                    } elseif (is_array($element)) {
                        $this->arrayToXml($element, $opts);
                    // object
                    } else {
                        $element->toXml($opts);
                    }
                }
            $tag->end();
        }
        return $builder->__toString();        
    }


    // formatting

    public function formatBoolean($boolean)
    {
        return $boolean ? 'true' : 'false';
    }
    
    public function formatBinary($binary)
    {
        return base64_encode($binary);
    }
    
    public function formatYaml($yaml)
    {
        return Horde_Yaml::dump($yaml);
    }
    
    public function formatDate($date)
    {
        // 0000-00-00 becomes NULL (http://bugs.php.net/bug.php?id=45647)
        if (preg_replace('/[^\d]/', '', $date) == 0) { return null; }

        $formatted = gmdate('Y-m-d', strtotime($date));
        return $formatted == '1970-01-01' ? null : $formatted;
    }
    
    public function formatDatetime($date)
    {
        // 0000-00-00 00:00:00 becomes NULL (http://bugs.php.net/bug.php?id=45647)
        if (preg_replace('/[^\d]/', '', $date) == 0) { return null; }

        $formatted = gmdate('c', strtotime($date));
        return substr($formatted, 0, 10) == '1970-01-01' ? null : $formatted;
    }


    // parsing

    public function parseSymbol($symbol, $entity = null)
    {
        return (string)$symbol;
    }

    public function parseDate($date, $entity = null)
    {
        // 0000-00-00 becomes NULL (http://bugs.php.net/bug.php?id=45647)
        if (preg_replace('/[^\d]/', '', $date) == 0) { return null; }

        // check if the date is valid
        $parsed = gmdate("Y-m-d", strtotime($date));
        if ($parsed == '1970-01-01') {
            return null;

        // of it's valid - return local time
        } else {
            return date("Y-m-d", strtotime($parsed));
        }
    }
    
    public function parseDatetime($datetime, $entity = null)
    {
        // 0000-00-00 00:00:00 becomes NULL (http://bugs.php.net/bug.php?id=45647)
        if (preg_replace('/[^\d]/', '', $datetime) == 0) { return null; }

        // check if the date is valid
        $parsed = gmdate("Y-m-d H:i:s", strtotime($datetime));
        if (substr($parsed, 0, 10) == '1970-01-01') {
            return null;

        // of it's valid - return local time
        } else {
            return date("Y-m-d H:i:s", strtotime($datetime));
        }
    }
    
    /**
     * Ruby has Fixnum and Bignum, and automatically converts between them.
     * PHP only has one Integer type and its maximum is determined by the
     * platform PHP is compiled for.  This causes a behavioral difference 
     * between this PHP version and its Rails counterpart.
     * 
     * On 32-bit PHP, the maximum Integer is 2147483647.  We throw an
     * Overflow exception if the XML to deserialize specifies an Integer
     * larger than this maximum.  On 64-bit PHP, we do not have this limit
     * and parsing an Integer works the same as the Rails version.
     */
    public function parseInteger($integer, $entity = null)
    {
        if ($integer == '') { return null; }

        $typecast  = (int)$integer;

        // check overflow
        $recast = (string)$typecast;
        if ($recast != $integer) {
            $msg = "String \"$integer\" was cast to Integer $typecast"; 
            throw new OverflowException($msg);
        }

        return $typecast;
    }
    
    public function parseFloat($float, $entity = null)
    {
        return (float)$float;
    }
    
    public function parseDouble($double, $entity = null)
    {
        return (double)$double;
    }
    
    public function parseDecimal($decimal, $entity = null)
    {
        return (float)$decimal;
    }
    
    public function parseBoolean($boolean, $entity = null)
    {
        if ($boolean == 'true') {
            return true;
        } elseif ($boolean == 'false') {
            return false;
        } else {
            return (boolean)$boolean;
        }
    }

    public function parseString($string, $entity = null)
    {
        return (string)$string;
    }

    public function parseYaml($yaml, $entity = null)
    {
        if (empty($yaml)) { return null; }

        return Horde_Yaml::load($yaml);
    }

    public function parseBase64Binary($bin, $entity = null)
    {
        return base64_decode($bin);
    }

    public function parseFile($file, $entity = null)
    {
        // default for name/content-type
        if (empty($entity['name'])) { 
            $entity['name'] = 'untitled'; 
        }
        if (empty($entity['content_type'])) { 
            $entity['content_type'] = 'application/octet-stream'; 
        }

        // Make an object that is polymorphic with Mad_Controller_File_Upload
        $path     = tempnam("/tmp", $entity['name']);
        $contents = base64_decode($file);
        file_put_contents($path, $contents);

        $file = array(
            'originalFilename' => $entity['name'], 
            'contentType'      => $entity['content_type'], 
            'length'           => filesize($path),
            'path'             => $path
        );
        return (object)$file;
    }

    
    // Protected
    
    /**
     * Create an array similar to that returned by xmlsimple in Ruby
     */
    protected function _parseElement($element)
    {
        $name    = $element->getName();
        $text    = preg_replace("/^\s+$/", "", (string)$element);
        $content = $text !== '' ? array('__content__' => $text) : array();

        $attrs = array();
        foreach ($element->attributes() as $key => $val) {
            $attrs[$key] = (string)$val;
        }

        $children = array();
        foreach ($element->children() as $child) {
            $childData = $this->_parseElement($child);
            $childName = $child->getName();

            if (isset($children[$childName])) {
                $children[$childName]   = array($children[$childName]);
                $children[$childName][] = $childData[$childName];
            } else {
                $children[$childName] = $childData[$childName];
            }
        }

        return array($name => array_merge($attrs, $content, $children));
    }
    
    /**
     * Typecast values based on their specified type
     * 
     * @param   array   $value
     * @return  array
     */
    protected function _typecastXmlValue($value)
    {
        // associative array
        if (is_array($value) && !is_int(key($value))) {
            // collection
            if (isset($value['type']) && $value['type'] == 'array') {
                $entries = array();
                foreach ($value as $k => $v) {
                    if ($k != 'type') { $entries[] =  $v; }
                }
                $entries = current($entries);

                // empty
                if (empty($entries) || (isset($value['__content__']) && empty($value['__content__']))) {
                    return array();

                } else {
                    // array
                    if (is_array($entries) && is_int(key($entries))) {
                        $result = array();
                        foreach ($entries as $v) {
                            $result[] = $this->_typecastXmlValue($v);
                        }
                        return $result;

                    // associative array
                    } elseif (is_array($entries)) {
                        return array($this->_typecastXmlValue($entries));
                    
                    // error
                    } else {
                        throw new Mad_Support_Exception("can't typecast ".gettype($entries));
                    }
                }

            // content
            } elseif (array_key_exists('__content__', $value)) {
                $type = isset($value['type']) ? $value['type'] : 'string';

                // parse data type
                if (isset($this->xmlParsing[$type])) {
                    $parser = $this->xmlParsing[$type];                    
                    return $this->$parser($value['__content__'], $value);

                } else {
                    return $value['__content__'];
                }

            // empty string
            } elseif ((isset($value['type']) && $value['type'] == 'string') && 
                      (empty($value['nil'])  || $value['nil'] != 'true')) {
                return '';

            // blank or nil parsed values are represented by null
            } elseif (empty($value) || (isset($value['nil']) && $value['nil'] == 'true')) {
                return null;

            // If the type is the only element which makes it then 
            // this still makes the value null, except if type is
            // a XML node(where type['value'] is a Hash)
            } elseif (isset($value['type']) && count($value) == 1 && !is_array($value['type'])) {
                return null;

            } else {
                $xmlValue = array();
                foreach ($value as $k => $v) {
                    $xmlValue[$k] = $this->_typecastXmlValue($v);
                }

                // Turn array('files' => array('file' => Object)) into 
                //      array('files' => Object) so it is compatible with
                // how multipart uploaded files from HTML appear
                if (isset($xmlValue["file"]) && is_object($xmlValue["file"])) {
                    return $xmlValue["file"];
                } else {
                    return $xmlValue;
                }
            }

        // array
        } elseif (is_array($value)) {
            $vals = array();
            foreach ($value as $val) {
                $vals[] = $this->_typecastXmlValue($val);
            }

            if (count($vals) == 0) {
                return null;
            } elseif (count($vals) == 1) {
                return current($vals);
            } else {
                return $vals;
            }

        // string
        } elseif (is_string($value)) {
            return $value;
            
        // error
        }  else {
            throw new Mad_Support_Exception("can't typecast ".gettype($value)." - $value");
        }
    }
    
    /**
     * Change all dashes to underscores in keys
     * 
     * @param   array   $params
     * @return  array
     */
    protected function _undasherizeKeys($params)
    {
        // associative array
        if (is_array($params) && !is_int(key($params))) {
            $result = array();
            foreach ($params as $k => $v) {
                $result[strtr($k, '-', '_')] = $this->_undasherizeKeys($v);
            }
            return $result;

        // array
        } elseif (is_array($params)) {
            $results = array();
            foreach ($params as $v) {
                $results[] = $this->_undasherizeKeys($v);
            }
            return $results;

        } else {
            return $params;
        }
    }
}
