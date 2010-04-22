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
class Mad_View_Helper_Form extends Mad_View_Helper_Base
{
    private $_instanceTag = 'Mad_View_Helper_Form_InstanceTag_Form';

    public function formFor($objectName)
    {
        $args = func_get_args();
        $options = (is_array(end($args))) ? array_pop($args) : array();

        if (isset($options['url'])) {
            $urlOptions = $options['url'];
            unset($options['url']);
        } else {
            $urlOptions = array();
        }

        if (isset($options['html'])) {
            $htmlOptions = $options['html'];
            unset($options['url']);
        } else {
            $htmlOptions = array();
        }
        echo $this->formTag($urlOptions, $htmlOptions);

        $options['end'] = '</form>';

        array_push($args, $options);
        return call_user_func_array(array($this, 'fieldsFor'), $args);
    }

    public function fieldsFor($objectName)
    {
        $args = func_get_args();
        $options = (is_array(end($args))) ? array_pop($args) : array();
        $object  = isset($args[1]) ? $args[1] : null;

        $builder = isset($options['builder']) ? $options['builder']
                                              : Mad_View_Base::$defaultFormBuilder;

        return new $builder($objectName, $object, $this->_view, $options);
    }

    public function textField($objectName, $method, $options = array())
    {
        $object = isset($options['object']) ? $options['object'] : null;
        unset($options['object']);
        $tag = new $this->_instanceTag($objectName, $method, $this->_view, $object);
        return $tag->toInputFieldTag('text', $options);
    }

    public function passwordField($objectName, $method, $options = array())
    {
        $object = isset($options['object']) ? $options['object'] : null;
        unset($options['object']);
        $tag = new $this->_instanceTag($objectName, $method, $this->_view, $object);
        return $tag->toInputFieldTag('password', $options);
    }

    public function hiddenField($objectName, $method, $options = array())
    {
        $object = isset($options['object']) ? $options['object'] : null;
        unset($options['object']);
        $tag = new $this->_instanceTag($objectName, $method, $this->_view, $object);
        return $tag->toInputFieldTag('hidden', $options);
    }

    public function fileField($objectName, $method, $options = array())
    {
        $object = isset($options['object']) ? $options['object'] : null;
        unset($options['object']);
        $tag = new $this->_instanceTag($objectName, $method, $this->_view, $object);
        return $tag->toInputFieldTag('file', $options);
    }

    public function checkBox($objectName, $method, $options = array(),
                                $checkedValue = '1', $uncheckedValue = '0')
    {
        $object = isset($options['object']) ? $options['object'] : null;
        unset($options['object']);
        $tag = new $this->_instanceTag($objectName, $method, $this->_view, $object);
        return $tag->toCheckBoxTag($options, $checkedValue, $uncheckedValue);
    }

    public function radioButton($objectName, $method, $tagValue, $options = array())
    {
        $object = isset($options['object']) ? $options['object'] : null;
        unset($options['object']);
        $tag = new $this->_instanceTag($objectName, $method, $this->_view, $object);
        return $tag->toRadioButtonTag($tagValue, $options);
    }

    public function textArea($objectName, $method, $options = array())
    {
        $object = isset($options['object']) ? $options['object'] : null;
        unset($options['object']);
        $tag = new $this->_instanceTag($objectName, $method, $this->_view, $object);
        return $tag->toTextAreaTag($options);
    }

    public function label($objectName, $method, $options = array())
    {
        $object = isset($options['object']) ? $options['object'] : null;
        unset($options['object']);
        $tag = new $this->_instanceTag($objectName, $method, $this->_view, $object);
        return $tag->toLabelTag($options);
    }

}
