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
        
        /**
         * Traverse all routes connected to the mapper in match order, 
         * and assemble an array of $routes used to build the output
         */
        $routes = array();
        foreach ($mapper->matchList as $route) {
          // name of this route, or empty string if anonymous
          $routeName = '';
          foreach ($mapper->routeNames as $name => $namedRoute) {
              if ($route === $namedRoute) { $routeName = $name; break; }
          }

          // request_method types recognized by this route, or empty string for any
          $methods = array('');
          if (isset($route->conditions['method']) && is_array($route->conditions['method']) ) {
            $methods = $route->conditions['method'];
          }

          // hardcoded defaults that can't be overriden by the request path as {:key=>"value"}
          $hardcodes = array();
          foreach ($route->hardCoded as $key) {
            $value = isset($route->defaults[$key]) ? $route->defaults[$key] : 'NULL';
            $dump = ":{$key}=>\"{$value}\"";
            ($key == 'controller') ? array_unshift($hardcodes, $dump) : $hardcodes[] = $dump;
          }
          $hardcodes = empty($hardcodes) ? '' : '{'. implode(', ', $hardcodes) .'}';  

          // route data for output 
          foreach ($methods as $method) {
            $routes[] = array('name'      => $routeName,
                              'method'    => $method,
                              'path'      => '/' . $route->routePath,
                              'hardcodes' => $hardcodes);
          }
        }

        // nothing to print?
        if (empty($routes)) { return; }

        /**
         * Find the max $widths to size the output columns {'name'=>40, 'method'=>6, ...}
         */
        $widths = array();
        foreach (array_keys($routes[0]) as $key) {
          $width = 0;
          foreach($routes as $r) { 
            $l = strlen($r[$key]);
            if ($l > $width) { $width = $l; }
          }
          $widths[$key] = $width;
        }

        /**
         * Print the output
         */
        foreach ($routes as $r) {
          echo str_pad($r['name'],   $widths['name'],   ' ', STR_PAD_LEFT),  ' ';
          echo str_pad($r['method'], $widths['method'], ' ', STR_PAD_RIGHT), ' ';
          echo str_pad($r['path'],   $widths['path'],   ' ', STR_PAD_RIGHT), ' ';
          echo $r['hardcodes'], PHP_EOL;
        }
    }

}
