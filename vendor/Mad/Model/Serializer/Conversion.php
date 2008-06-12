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
      "binary"   => 'formatBinary',
      "yaml"     => 'formatYaml',
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
      "yaml"         => 'parseYaml',
      "base64Binary" => 'parseBase64Binary',
      "file"         => 'parseFile',
    );
    

    // formatting

    public function formatBinary($binary)
    {
        return base64encode($binary);
    }

    public function formatYaml($yaml)
    {
        return $yaml;
    }


    // parsing

    public function parseDate($date)
    {
        return $date;
    }
    
    public function parseDatetime($datetime)
    {
        return $datetime;
    }
    
    public function parseInteger($integer)
    {
        return $integer;
    }
    
    public function parseFloat($float)
    {
        return $float;
    }
    
    public function parseDouble($double)
    {
        return $double;
    }
    
    public function parseDecimal($decimal)
    {
        return $decimal;
    }
    
    public function parseBoolean($boolean)
    {
        return $boolean;
    }
    
    public function parseString($string)
    {
        return $string;
    }
    
    public function parseYaml($yaml)
    {
        return $yaml;
    }
    
    public function parseBase64Binary($bin)
    {
        return $bin;
    }

    public function parseFile($file)
    {
        return $file;
    }

}
