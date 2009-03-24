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
class Mad_View_Helper_Form_InstanceTag_Date extends Mad_View_Helper_Form_InstanceTag_Base
{
    public function toDateSelectTag($options = array())
    {
        $options = array_merge($options, array('discardHour' => true));
        return $this->_dateOrTimeSelect($options);
    }

    private function _dateOrTimeSelect($options)
    {
        $defaults = array('discardType' => true);
        $options = array_merge($defaults, $options);

        $datetime = $this->value($this->object());
        if (! isset($datetime) && ! isset($options['includeBlank'])) {
            $datetime = time();
        }

        $position = array('year' => 1, 'month' => 2, 'day' => 3,
                          'hour' => 4, 'minute' => 5, 'second' => 6);
        
        if (! isset($options['order'])) {
            $options['order'] = array('year', 'month', 'day');
        }
        $order = $options['order'];
        
        // Discard explicit and implicit by not being included in the 'order'
        $discard = array();
        if (isset($options['discardYear']) || !in_array('year', $order)) {
            $discard['year'] = true;
        }
        if (isset($options['discardMonth']) || !in_array('month', $order)) {
            $discard['month'] = true;
        }
        if (isset($options['discardDay']) || isset($discard['month']) || !in_array('day', $order)) {
            $discard['day'] = true;
        }
        if (isset($options['discardHour'])) {
            $discard['hour'] = true;
        }
        if (isset($options['discardMinute']) || isset($discard['hour'])) {
            $discard['minute'] = true;
        }
        if (!isset($options['includeSeconds']) || isset($discard['minute'])) {
            $discard['second'] = true;
        }

        // Maintain valid dates by including hidden fields for discarded elements
        foreach(array('day', 'month', 'year') as $o) {
            if (! in_array($o, $order)) {
                array_unshift($order, $o);
            }
        }

        // Ensure proper ordering of 'hour', 'minute', and 'second'
        foreach(array('day', 'minute', 'second') as $o) {
            // @todo
            //           [:hour, :minute, :second].each { |o| order.delete(o); order.push(o) }
        }

        $dateOrTimeSelect = '';
        $order = array_reverse($order);
        foreach ($order as $param) {
            // Send hidden fields for discarded elements once output has started
            // This ensures AR can reconstruct valid dates using ParseDate
            if (isset($discard[$param]) && !strlen($dateOrTimeSelect)) {
                continue;
            }

            $selectMethod = "select" . ucfirst($param);
            $options = array_merge($options, array('useHidden' => $discard[$param]));
            $optionsWithPrefix = $this->_optionsWithPrefix($position[$param], $options);
            
            $dateOrTimeSelect = $this->$selectMethod($dateTime, $optionsWithPrefix)
                              . $dateOrTimeSelect;

            switch($param) {
                case 'hour':
                    $insert = ($discard['year'] && $discard['day'] ? '' : ' &mdash; ');
                    break;
                case 'minute':
                    $insert = ' : ';
                    break;
                case 'second':
                    $insert = (isset($options['includeSeconds']) ? ' : ' : '');
                    break;
            }
            
            $dateOrTimeSelect = $insert . $dateOrTimeSelect;
        }

        return $dateOrTimeSelect;
    }

    private function _optionsWithPrefix($position, $options)
    {
        $prefix = $this->objectName;
        if (isset($options['index'])) {
            $prefix .= $options['index'];
        } else if ($this->autoIndex) {
            $prefix .= $this->autoIndex;
        }
        
        $prefix = "{$prefix}[{$this->objectProperty}]({$position}i)";
        $options = array_merge($options, array('prefix' => $prefix));
        
        return $options;
    }
}
