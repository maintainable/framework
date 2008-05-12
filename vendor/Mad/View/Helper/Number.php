<?php
/**
 * Provides methods for converting a numbers into formatted strings.
 * Methods are provided for phone numbers, currency, percentage,
 * precision, positional notation, and file size.
 *
 * @category   Mad
 * @package    Mad_View
 * @subpackage Helper
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */

/**
 * Provides methods for converting a numbers into formatted strings.
 * Methods are provided for phone numbers, currency, percentage,
 * precision, positional notation, and file size.
 *
 * @category   Mad
 * @package    Mad_View
 * @subpackage Helper
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_View_Helper_Number extends Mad_View_Helper_Base
{
   /**
    * Formats a $number into a US phone number. You can customize the format
    * in the $options array:
    *  - <tt>areaCode</tt>    - Adds parentheses around the area code.
    *  - <tt>delimiter</tt>   - Specifies the delimiter to use, defaults to "-".
    *  - <tt>extension</tt>   - Specifies an extension to add to the end of the
    *                           generated number
    *  - <tt>countryCode</tt> - Sets the country code for the phone number.
    * 
    * $this->numberToPhone(1235551234)                              => 123-555-1234
    * $this->numberToPhone(1235551234, array('areaCode' => true))   => (123) 555-1234
    * $this->numberToPhone(1235551234, array('delimiter' => " "))   => 123 555 1234
    * $this->numberToPhone(1235551234, array('areaCode'  => true, 
    *                                        'extension' => 555))   => (123) 555-1234 x 555
    * $this->numberToPhone(1235551234, array('countryCode => 1))    => +01 (123) 555-1234
    *
    * @param  integer  $number    Phone number to format
    * @param  array    $options   Format options
    * @return string              Formatted phone number
    */
    public function numberToPhone($number, $options = array())
    {
        $areaCode    = isset($options['areaCode'])  ? $options['areaCode'] : null;
        $delimiter   = isset($options['delimiter']) ? $options['delimiter'] : '-';
        $extension   = isset($options['extension']) ? trim($options['extension']) : null;
        $countryCode = isset($options['countryCode']) ? $options['countryCode'] : null;
        
        $str = '';

        if ($countryCode) {
            $str .= "+$countryCode$delimiter";
        }

        if ($areaCode) {
            $str .= preg_replace('/([0-9]{1,3})([0-9]{3})([0-9]{4}$)/',
                                 "(\\1) \\2$delimiter\\3", $number);
        } else {
            $str .= preg_replace('/([0-9]{1,3})([0-9]{3})([0-9]{4})$/',
                                 "\\1$delimiter\\2$delimiter\\3", $number);
        }
        
        if (strlen($extension)) {
            $str .= " x $extension";
        }
        
        return $str;
    }

    /**
     * Formats a $number into a currency string. You can customize the format
     * in the $options array.
     *
     *  - <tt>precision</tt>  - Sets the level of precision, defaults to 2
     *  - <tt>unit</tt>       - Sets the denomination of the currency, defaults to "$"
     *  - <tt>separator</tt>  - Sets the separator between the units, defaults to "."
     *  - <tt>delimiter</tt>  - Sets the thousands delimiter, defaults to ","
     *
     *  $this->numberToCurrency(1234567890.50)     => $1,234,567,890.50
     *  $this->numberToCurrency(1234567890.506)    => $1,234,567,890.51
     *  $this->numberToCurrency(1234567890.506, array('precision' => 3))  => $1,234,567,890.506
     *  $this->numberToCurrency(1234567890.50,  array('unit' => "&pound;", 
     *                                                'separator' => ",", 
     *                                                'delimiter' => "")  => &pound;1234567890,50
     *
     * @param  float    $number   Currency value to format
     * @param  options  $options  Format options
     * @return string             Formatted currency value
     */
    public function numberToCurrency($number, $options = array())
    {
        if (! strlen($number)) {
            return $number;
        }
        
        $precision = isset($options['precision']) ? $options['precision'] : 2;
        $unit      = isset($options['unit']) ? $options['unit'] : '$';
        $delimiter = isset($options['delimiter']) ? $options['delimiter'] : ',';
        if ($precision > 0) {
            $separator = isset($options['separator']) ? $options['separator'] : '.'; 
        } else {
            $separator = '';
        }

        $parts = explode('.', $this->numberWithPrecision($number, $precision));
        return $unit . $this->numberWithDelimiter($parts[0], $delimiter)
                     . $separator . (isset($parts[1]) ? $parts[1] : '');
    }
    
    /**
     * Formats a $number as a percentage string. You can customize the
     * format in the $options array.
     *
     *  - <tt>precision</tt>  - Sets the level of precision, defaults to 3
     *  - <tt>separator</tt>  - Sets the separator between the units, defaults to "."
     *
     *  $this->numberToPercentage(100)    => 100.000%
     *  $this->numberToPercentage(100, array('precision' => 0))   => 100%
     *  $this->numberToPercentage(302.0574, array('precision' => 2))   => 302.06%
     *
     * @param  integer|float  $number   Number to format to a percentage
     * @param  array          $options  Format options
     * @return string                   Formatted percentage value
     */
    public function numberToPercentage($number, $options = array())
    {
        if (! strlen($number)) {
            return $number;
        }
        
        $precision = isset($options['precision']) ? $options['precision'] : 3;
        $separator = isset($options['separator']) ? $options['separator'] : '.';
        
        $number = $this->numberWithPrecision($number, $precision);
        $parts = explode('.', $number);
        
        if (! isset($parts[1])) {
            return $parts[0] .= '%';
        } else {
            return $parts[0] . $separator . $parts[1] . '%';
        }
    }
    
    /**
     * Formats a $number with grouped thousands using $delimiter. You
     * can customize the format using optional $delimiter and 
     * $separator parameters.
     *
     *  $this->numberWithDelimiter(12345678)        => 12,345,678
     *  $this->numberWithDelimiter(12345678.05)     => 12,345,678.05
     *  $this->numberWithDelimiter(12345678, ".")   => 12.345.678    
     *
     * @param  integer|float  $number     Number to format
     * @param  string         $delimiter  Sets the thousands delimiter, defaults to ","
     * @param  string         $separator  Sets the separator between the units, defaults to "."
     * @return string                     Formatted number
     */
    public function numberWithDelimiter($number, $delimiter = ',', $separator = '.')
    {
        if (! strlen($number)) {
            return $number;
        }
                
        $parts = explode('.', (string)$number);
        $parts[0] = preg_replace('/(\d)(?=(\d\d\d)+(?!\d))/',
                                 "\\1$delimiter", $parts[0]);
        return implode($separator, $parts);
    }
  
    /** 
     * Formats a $number with the specified level of $precision. 
     * The default level of precision is 3.
     *
     * $this->numberWithPrecision(111.2345)    => 111.235
     * $this->numberWithPrecision(111.2345, 2) => 111.24
     *
     * @param  integer|float  $number    Number to format
     * @param  integer        $precison  Level of precision
     * @return string                    Formatted number
     */
    public function numberWithPrecision($number, $precision = 3) {
        if (is_numeric($number)) {
            return sprintf("%01.{$precision}f", $number);
        } else {
            return $number;
        }
    }
    
    /** 
     * Formats the bytes in $size into a more understandable representation.
     * Useful for reporting file sizes to users. This method returns NULL if
     * $size cannot be converted into a number. You can change the default
     * precision of 1 in $precision.
     *
     *   $this->numberToHumanSize(123)           => 123 Bytes
     *   $this->numberToHumanSize(1234)          => 1.2 KB
     *   $this->numberToHumanSize(12345)         => 12.1 KB
     *   $this->numberToHumanSize(1234567)       => 1.2 MB
     *   $this->numberToHumanSize(1234567890)    => 1.1 GB
     *   $this->numberToHumanSize(1234567890123) => 1.1 TB
     *   $this->numberToHumanSize(1234567, 2)    => 1.18 MB
     *
     * @param  integer|float  $size        Size to format
     * @param  integer        $preceision  Level of precision
     * @return string                      Formatted size value
     */
    public function numberToHumanSize($size, $precision = 1) {
        if (! is_numeric($size)) {
            return null;
        }

        if ($size == 1) {
            $size = '1 Byte';
        } else if ($size < 1024) {
            $size = sprintf('%d Bytes', $size);
        } else if ($size < 1048576) {
            $size = sprintf("%.{$precision}f KB", $size / 1024);
        } else if ($size < 1073741824) {
            $size = sprintf("%.{$precision}f MB", $size / 1048576);
        } else if ($size < 1099511627776) {
            $size = sprintf("%.{$precision}f GB", $size / 1073741824);
        } else {
            $size = sprintf("%.{$precision}f TB", $size / 1099511627776);
        }

        return str_replace('.0', '', $size);
    }
}
