<?php
/**
 * @category   Mad
 * @package    Mad_Model
 * @subpackage ConnectionAdapter
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */

/**
 * @category   Mad
 * @package    Mad_Model
 * @subpackage ConnectionAdapter
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_Model_ConnectionAdapter_Mysql_TableDefinition extends Mad_Model_ConnectionAdapter_Abstract_TableDefinition
{
    /**
     * @param   string  $name
     * @param   array   $options
     */
    public function end()
    {
        if (empty($this->_options['temporary'])) {
            $this->_options['options'] = 'ENGINE=InnoDB';
        }
        return parent::end();
    }
}