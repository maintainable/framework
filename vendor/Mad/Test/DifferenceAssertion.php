<?php
/**
 * @category   Mad
 * @package    Mad_Test
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */

/**
 * Assert that the results of an expression are different before and after
 * a block of code is executed. 
 *
 * @category   Mad
 * @package    Mad_Test
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_Test_DifferenceAssertion extends Mad_Test_Unit
{
    /**
     * @param   string  $expression
     * @param   integer $difference
     * @param   string  $msg
     */
    public function __construct($expression, $difference = 1, $msg = null)
    {
        $this->_expression = $expression;
        $this->_difference = $difference;
        $this->_msg        = $msg;

        // add semi-colon if it's missing
        if (substr($this->_expression, -1, 1) != ';') {
            $this->_expression = "$this->_expression;";
        }

        // initial value
        eval('$this->_before = '.$this->_expression);
    }

    /**
     * perform assertion
     */
    public function end()
    {
        // final value
        eval('$after = '.$this->_expression);

        // assertion
        $this->assertEquals($this->_difference, $after - $this->_before, $this->_msg);
    }
}