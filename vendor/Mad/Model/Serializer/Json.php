<?php
/**
 * @category   Mad
 * @package    Mad_Model
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * The base object from which all DataObjects are extended from
 *
 * @category   Mad
 * @package    Mad_Model
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Model_Serializer_Json extends Mad_Model_Serializer_Base
{
    
    public function serialize() 
    {
        $serializedArray = $this->getSerializableRecord();

        $solarJson = new Solar_Json(array());
        return $solarJson->encode($serializedArray);
    }
}
