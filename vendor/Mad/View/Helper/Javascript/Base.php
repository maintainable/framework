<?php
/**
 * @category   Mad
 * @package    Mad_View
 * @subpackage Helper
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */

/**
 * Dumps a variable for inspection.
 * Portions borrowed from Paul M. Jones' Solar_Debug
 *
 * @category   Mad
 * @package    Mad_View
 * @subpackage Helper
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_View_Helper_Javascript_Base extends Mad_View_Helper_Base
{
    protected function _optionsForJavascript($options)
    {
        foreach ($options as $k => &$v) {
            if (is_float($v)) {
                $v = rtrim(sprintf('%.2f', $v), '0');
                if (substr($v, -1) == '.') {
                    $v .= '0';
                }
            }

            if (is_bool($v)) {
                $v = ($v) ? 'true' : 'false';
            }

            $v = "$k:$v";
        }
        sort($options);
        $options = implode(', ', $options);
        return '{' . $options . '}'; 
    }
    
    protected function _arrayOrStringForJavascript($options)
    {
        if (is_array($options)) {
            $options = implode("','", $options);
            return "['$options']";
        } else if ($options !== null) {
            return "'$options'";
        } else {
            return '';
        }
    }
}
