<?php
/**
 * @category   Mad
 * @package    Mad_Model
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */

/**
 * The base object from which all DataObjects are extended from
 *
 * @category   Mad
 * @package    Mad_Model
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_Model_Serializer_Conversion
{
    public $xmlFormatting = array(
      "binary" => 'formatBinary',
    );
    
    public $xmlParsing = array(
      "date"         => 'parseDate',
      "datetime"     => 'parseDatetime',
      "dateTime"     => 'parseDatetime',
      "integer"      => 'parseInteger',
      "float"        => 'parseFloat',
      "double"       => 'parseDouble',
      "decimal"      => 'parseDecimal',
      "boolean"      => 'parseBoolean',
      "string"       => 'parseString',
      "base64Binary" => 'parseBase64Binary',
      "file"         => 'parseFile',
    );

    
    // Convert from xml to attribures array
    public function fromXml($xml)
    {
        
        // @todo - replace this stub 
        $values = array('article' => array('id' => '1', 'title' => 'Easier XML-RPC for PHP5'));
        return $values;
    }


    // formatting

    public function formatBinary($binary)
    {
        return base64_encode($binary);
    }


    // parsing

    public function parseDate($date)
    {
        return date("Y-m-d", strtotime($date));
    }
    
    public function parseDatetime($datetime)
    {
        return date("Y-m-d H:i:s", strtotime($datetime));
    }
    
    public function parseInteger($integer)
    {
        return (int)$integer;
    }
    
    public function parseFloat($float)
    {
        return (float)$float;
    }
    
    public function parseDouble($double)
    {
        return (double)$double;
    }
    
    public function parseDecimal($decimal)
    {
        return (float)$decimal;
    }
    
    public function parseBoolean($boolean)
    {
        if ($boolean == 'true') {
            return true;
        } elseif ($boolean == 'false') {
            return false;
        } else {
            return (boolean)$boolean;
        }
    }

    public function parseString($string)
    {
        return (string)$string;
    }

    public function parseBase64Binary($bin)
    {
        return base64_decode($bin);
    }

    public function parseFile($file)
    {
        return $file;
    }


    // Protected
    
    protected function _typecastXmlValue($value)
    {

    }

    protected function _undasherizeKeys($params)
    {

    }
}
