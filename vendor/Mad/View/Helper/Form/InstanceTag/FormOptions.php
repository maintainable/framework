<?php
/**
 * @category   Mad
 * @package    Mad_View
 * @subpackage Helper
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * @category   Mad
 * @package    Mad_View
 * @subpackage Helper
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_View_Helper_Form_InstanceTag_FormOptions extends Mad_View_Helper_Form_InstanceTag_Base
{
    public function toSelectTag($choices, $options, $htmlOptions)
    {
        $htmlOptions = $this->addDefaultNameAndId($htmlOptions);
        $value = $this->value($this->object());
        $selectedValue = isset($options['selected']) ? $options['selected'] : $value;
        // @todo finish me
    }

    private function _addOptions($optionTags, $options, $value = null)
    {
        if (isset($options['includeBlank'])) {
            $optionTags = "<option value=\"\"></option>\n" . $optionTags;
        }
        
        if (! strlen($value) && isset($options['prompt'])) {
            $option = is_string($options['prompt']) ? $options['prompt'] : 'Please select';
            $optionTags = "<option value=\"\">$option</option>\n" . $optionTags;
        } 
        
        return $optionTags;
    }
}
