<?php
/**
 * @category   Mad
 * @package    Mad_Task
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * Built-in framework tasks for viewing routes.
 *
 * @category   Mad
 * @package    Mad_Task
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Task_BuiltinSet_Routes extends Mad_Task_Set
{
    /**
     * Print out all defined routes in match order, with names.
     */
    public function routes()
    {
        $dispatcher = Mad_Controller_Dispatcher::getInstance();
        $mapper = $dispatcher->getRouteMapper();
    
        $mapper->utils->printRoutes();
    }

}
