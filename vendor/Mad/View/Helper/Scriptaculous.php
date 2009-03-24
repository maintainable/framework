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
class Mad_View_Helper_Scriptaculous extends Mad_View_Helper_Javascript_Base
{
    private $_toggleEffects = array('toggleAppear', 'toggleSlide', 'toggleBlind');
    
    
    public function visualEffect($name, $elementId = false, $jsOptions = array()) {
        $element = ($elementId ? $this->jsonEncode($elementId) : 'element');

        if (isset($jsOptions['queue'])) {
            if (is_array($jsOptions['queue'])) {
                foreach($jsOptions['queue'] as $k=>&$v) {
                    $v = ($k == 'limit' ? "$k:$v" : "$k:'$v'");
                }
                $jsOptions['queue'] = '{' . implode(',', $jsOptions['queue']) . '}';
            } else {
                $jsOptions['queue'] = "'" . $jsOptions['queue'] . "'";
            }
        }        

        $jsOptions = $this->_optionsForJavascript($jsOptions); 

        if (in_array($name, $this->_toggleEffects)) {
            $toggle  = strtolower(str_replace('toggle', '', $name));
            return "Effect.toggle($element,'$toggle',$jsOptions);";
        } else {
            $name = ucfirst($name);
            return "new Effect.$name($element,$jsOptions);";
        }
    }
    
    public function sortableElement($elementId, $options = array())
    {
        $js = Mad_Support_Base::chopToNull($this->sortableElementJs($elementId, $options));
        return $this->javascriptTag($js);
    }

    // @todo nodoc
    public function sortableElementJs($elementId, $options = array()) 
    {
        if (! isset($options['with'])) {
            $options['with'] = 'Sortable.serialize(' . $this->jsonEncode($elementId) . ')';
        }
        
        if (! isset($options['onUpdate'])) {
            $options['onUpdate'] = 'function(){' . $this->remoteFunction($options) . '}';
        }

        $ajaxOptions = array_flip($this->getPrototypeAjaxOptions());
        $options = array_diff_key($options, $ajaxOptions);

        foreach (array('tag', 'overlap', 'constraint', 'handle') as $option) {
            if (isset($options[$option])) {
                $options[$option] = "'" . $options[$option] . "'";
            }
        }
        
        if (isset($options['containment'])) {
            $options['containment'] = $this->_arrayOrStringForJavascript($options['containment']);
        }
        
        if (isset($options['only'])) {
            $options['only'] = $this->_arrayOrStringForJavascript($options['only']);
        }
        
        $elementId = $this->jsonEncode($elementId);
        $options   = $this->_optionsForJavascript($options);

        return "Sortable.create($elementId, $options);";
    }
    
    public function draggableElement($elementId, $options = array())
    {
        $js = Mad_Support_Base::chopToNull($this->draggableElementJs($elementId, $options));
        return $this->javascriptTag($js);        
    }
    
    // @todo nodoc
    public function draggableElementJs($elementId, $options = array())
    {
        $elementId = $this->jsonEncode($elementId);
        $options   = $this->_optionsForJavascript($options);

        return "new Draggable($elementId, $options);";
    }
    
    public function dropReceivingElement($elementId, $options = array())
    {
        $js = Mad_Support_Base::chopToNull($this->dropReceivingElementJs($elementId, $options));
        return $this->javascriptTag($js);           
    }
    
    public function dropReceivingElementJs($elementId, $options = array())
    {
        if (! isset($options['with'])) {
            $options['with'] = "'id=' + encodeURIComponent(element.id)";
        }

        if (! isset($options['onDrop'])) {
            $options['onDrop'] = "function(element){" . $this->remoteFunction($options) . '}';
        }
        
        $ajaxOptions = array_flip($this->getPrototypeAjaxOptions());
        $options = array_diff_key($options, $ajaxOptions);
        
        if (isset($options['accept'])) {
            $options['accept'] = $this->_arrayOrStringForJavascript($options['accept']);
        }
        
        if (isset($options['hoverclass'])) {
            $options['hoverclass'] = "'{$options['hoverclass']}'";
        }
        
        $elementId = $this->jsonEncode($elementId);
        $options   = $this->_optionsForJavascript($options);

        return "Droppables.add($elementId, $options);";        
    }
}


