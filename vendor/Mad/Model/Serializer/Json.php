<?php
/**
 * @category   Mad
 * @package    Mad_Model
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * The base object from which all DataObjects are extended from
 *
 * @category   Mad
 * @package    Mad_Model
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Model_Serializer_Json extends Mad_Model_Serializer_Base
{
    
    public function serialize() 
    {
        if (! function_exists('json_decode')) { 
            throw new Mad_Model_Exception('json_decode() function required');
        }
        
        $serializedArray = $this->getSerializableRecord();
        return json_encode($serializedArray);
    }

}
