<?php
/**
 * @category   Mad
 * @package    Mad_View
 * @subpackage Helper
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */

/**
 * @category   Mad
 * @package    Mad_View
 * @subpackage Helper
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_View_Helper_Model extends Mad_View_Helper_Base
{
    public function errorMessageOn($objectName, $property, 
                                   $prependText='', $appendText='', $cssClass='formError')
    {
        if (($obj = $this->{$objectName}) && ($errors = $obj->errors->on($property))) {
            if (is_array($errors)) {
                $errors = $errors[0];
            }
            return $this->contentTag('div', 
                                     "{$prependText}{$errors}{$appendText}",
                                     array('class' => $cssClass));
        } else {
            return '';
        }
    }

    public function errorMessagesFor()
    {
        $params = func_get_args();
        $options = (is_array(end($params))) ? array_pop($params) : array();

        $objects = array();
        $count   = 0;
        foreach ($params as $objectName) {
            $obj = $this->{$objectName};
            if (isset($obj)) {
                $objects[] = $obj;
                $count += count($obj->errors);
            }
        }

        if (! $count) {
            return '';
        }
            
        $htmlOptions = array();
        foreach(array('id', 'class') as $key) {
            if (array_key_exists($key, $options)) {
                $value = $options[$key];
                if (strlen($value)) {
                    $htmlOptions[$key] = $value;
                }
            } else {
                $htmlOptions[$key] = 'errorExplanation';
            }
        }

        if (empty($options['objectName'])) {
            $options['objectName'] = $params[0];
        }

        $headerMessage = $this->pluralize($count, 'error')
                       . ' prohibited this '
                       . str_replace('_', ' ', $options['objectName'])
                       . ' from being saved';

        $errorMessages = '';
        foreach ($objects as $object) {
            foreach ($object->errors->fullMessages() as $msg) {
                $errorMessages .= $this->contentTag('li', $msg);
            }
        }

        if (empty($options['headerTag'])) {
            $options['headerTag'] = 'h2';
        }

        $contentForDiv = $this->contentTag($options['headerTag'], $headerMessage)
                       . $this->contentTag('p', 'There were problems with the following fields:')
                       . $this->contentTag('ul', $errorMessages);
        return $this->contentTag('div', $contentForDiv, $htmlOptions);
    }
}
