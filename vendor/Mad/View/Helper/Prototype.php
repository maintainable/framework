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
class Mad_View_Helper_Prototype extends Mad_View_Helper_Javascript_Base
{
    private $_callbacks   = null;
    private $_ajaxOptions = null;

    // @todo nodoc
    // @see ActionView::Helpers::PrototypeHelper::CALLBACKS
    public function getPrototypeCallbacks()
    {
        if ($this->_callbacks === null) {
            $this->_callbacks = array('uninitialized', 'loading', 'loaded', 
                                      'interactive', 'complete', 'failure', 'success');
            $this->_callbacks = array_merge($this->_callbacks, range(100,599));
        }

        return $this->_callbacks;
    }
    
    // @todo nodoc
    // @see ActionView::Helpers::PrototypeHelper::AJAX_OPTIONS
    public function getPrototypeAjaxOptions()
    {
        if ($this->_ajaxOptions === null) {
            $this->_ajaxOptions = array('before', 'after', 'condition', 'url', 
                                        'asynchronous', 'method', 'insertion', 
                                        'position', 'form', 'with', 'update', 'script');
            $this->_ajaxOptions = array_merge($this->_ajaxOptions, 
                                              $this->getPrototypeCallbacks());
        }
        
        return $this->_ajaxOptions;
    }
    
    public function remoteFunction($options)
    {
        $javascriptOptions = $this->_optionsForAjax($options);

        $update = '';
        if (isset($options['update']) && is_array($options['update'])) {
            $update = array();

            if (isset($options['update']['success'])) {
                $update[] = "success:'{$options['update']['success']}'";
            }

            if (isset($options['update']['failure'])) {
                $update[] = "failure:'{$options['update']['success']}'";
            }
            
            $update = '{' . implode(',', $update) . '}';
        } else if (isset($options['update'])) {
            $update .= "'{$options['update']}'";
        }

        $function = empty($update) ? "new Ajax.Request("
                                   : "new Ajax.Updater($update, ";

        $urlOptions = isset($options['url']) ? $options['url'] : null;
        if (is_array($urlOptions)) {
            $urlOptions = array_merge($urlOptions, array('escape' => false));
        }
        $function .= "'" . $this->urlFor($urlOptions) . "'";
        $function .= ", $javascriptOptions)";

        if (isset($options['before'])) {
            $function = "{$options['before']}; $function";
        }
        
        if (isset($options['after'])) {
            $function = "$function; {$options['after']}";
        }
        
        if (isset($options['condition'])) {
            $function = "if ({$options['condition']}) { $function; }";
        }
        
        if (isset($options['confirm'])) {
            $confirm = $this->escapeJavascript($options['confirm']);
            $function = "if (confirm('$confirm')) { $function; }";
        }
        
        return $function;
    }
    
    private function _optionsForAjax($options)
    {
        $jsOptions = $this->_buildCallbacks($options);

        if (!isset($options['type'])) {
            $jsOptions['asynchronous'] = true;
        } else {
            $jsOptions = ($options['type'] == 'synchronous') ? 'synchronous' : 'asynchronous';
        }

        if (isset($options['method'])) {
            $jsOptions['method'] = $this->_methodOptionToString($options['method']);
        }
        
        if (isset($options['insertion'])) {
            $position = ucfirst(isset($options['position']) ? $options['position'] : null);
            $jsOptions['insertion'] = "Insertion.$position";
        }

        if (! isset($options['script'])) {
            $jsOptions['evalScripts'] = true;
        } else {
            $jsOptions['script'] = $options['script'];
        }

        if (isset($options['form'])) {
            $jsOptions['parameters'] = 'Form.serialize(this)';
        } else if (isset($options['submit'])) {
            $jsOptions['parameters'] = "Form.serialize('{$options['submit']}')";
        } else if (isset($options['with'])) {
            $jsOptions['parameters'] = isset($options['with']) ? $options['with'] : null;
        }
        
        return $this->_optionsForJavascript($jsOptions);
    }

    private function _methodOptionToString($method)
    {
        if (is_string($method) && strpos($method, "'" === false)) {
            return $method;
        } else {
            return "'$method'";
        }
    }

    private function _buildObserver($klass, $name, $options = array())
    {
        if (isset($options['with']) && strpos($options['with'], '=') === false) {
            $options['with'] = "'{$options['with']}=' + value";
        } else {
            if ($options['update'] && !isset($options['with'])) {
                $options['with'] = 'value';
            }
        }
        
        $callback = isset($options['function']) ? $options['function']
                                                : $this->remoteFunction($options);

        $javascript = "new $klass('$name', ";
        if (isset($options['frequency'])) {
            $javascript .= "{$options['frequency']}, ";
        }
        $javascript .= "function(element, value) {"
                     . "$callback}";
        if (isset($options['on'])) {
            $javascript .= ", '{$options['on']}'";
        }
        $javascript .= ')';
        
        return $this->javascriptTag($javascript);
    }

    private function _buildCallbacks($options)
    {
        $callbacks = array();
        foreach ($options as $callback => $code) {
            if (in_array($callback, $this->getPrototypeCallbacks())) {
                $name = 'on' + ucfirst($callback);
                $callbacks[$name] = "function(request){$code}";
            }
        }
        return $callbacks;
    }

}
