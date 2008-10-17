<?php
/**
 * @category   Mad
 * @package    Mad_Madness
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * @category   Mad
 * @package    Mad_Madness
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Madness_Initializer
{
    public static function run()
    {
        return Mad_Madness_Configuration::getInstance();
    }
}