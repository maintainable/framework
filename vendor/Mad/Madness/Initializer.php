<?php
/**
 * @category   Mad
 * @package    Mad_Madness
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */

/**
 * @category   Mad
 * @package    Mad_Madness
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_Madness_Initializer
{
    public static function run()
    {
        return Mad_Madness_Configuration::getInstance();
    }
}