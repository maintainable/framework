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
        // typecast_xml_value(undasherize_keys(XmlSimple.xml_in_string(xml,
        //   'forcearray'   => false,
        //   'forcecontent' => true,
        //   'keeproot'     => true,
        //   'contentkey'   => '__content__')
        // ))
        
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
      //case value.class.to_s
      //  when 'Hash'
      //    if value['type'] == 'array'
      //      child_key, entries = value.detect { |k,v| k != 'type' }   # child_key is throwaway
      //      if entries.nil? || (c = value['__content__'] && c.blank?)
      //        []
      //      else
      //        case entries.class.to_s   # something weird with classes not matching here.  maybe singleton methods breaking is_a?
      //        when "Array"
      //          entries.collect { |v| typecast_xml_value(v) }
      //        when "Hash"
      //          [typecast_xml_value(entries)]
      //        else
      //          raise "can't typecast #{entries.inspect}"
      //        end
      //      end
      //    elsif value.has_key?("__content__")
      //      content = value["__content__"]
      //      if parser = XML_PARSING[value["type"]]
      //        if parser.arity == 2
      //          XML_PARSING[value["type"]].call(content, value)
      //        else
      //          XML_PARSING[value["type"]].call(content)
      //        end
      //      else
      //        content
      //      end
      //    elsif value['type'] == 'string' && value['nil'] != 'true'
      //      ""
      //    # blank or nil parsed values are represented by nil
      //    elsif value.blank? || value['nil'] == 'true'
      //      nil
      //    # If the type is the only element which makes it then 
      //    # this still makes the value nil, except if type is
      //    # a XML node(where type['value'] is a Hash)
      //    elsif value['type'] && value.size == 1 && !value['type'].is_a?(::Hash)
      //      nil
      //    else
      //      xml_value = value.inject({}) do |h,(k,v)|
      //        h[k] = typecast_xml_value(v)
      //        h
      //      end
      //      
      //      # Turn { :files => { :file => #<StringIO> } into { :files => #<StringIO> } so it is compatible with
      //      # how multipart uploaded files from HTML appear
      //      xml_value["file"].is_a?(StringIO) ? xml_value["file"] : xml_value
      //    end
      //  when 'Array'
      //    value.map! { |i| typecast_xml_value(i) }
      //    case value.length
      //      when 0 then nil
      //      when 1 then value.first
      //      else value
      //    end
      //  when 'String'
      //    value
      //  else
      //    raise "can't typecast #{value.class.name} - #{value.inspect}"
      //end
    }

    protected function _undasherizeKeys($params)
    {
      // case params.class.to_s
      //   when "Hash"
      //     params.inject({}) do |h,(k,v)|
      //       h[k.to_s.tr("-", "_")] = undasherize_keys(v)
      //       h
      //     end
      //   when "Array"
      //     params.map { |v| undasherize_keys(v) }
      //   else
      //     params
      // end
    }
}
