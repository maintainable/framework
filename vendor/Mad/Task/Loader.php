<?php
/**
 * @category   Mad
 * @package    Mad_Task
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * Recursively loads tasks files from directories.
 *
 * @category   Mad
 * @package    Mad_Task
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Task_Loader
{
    /**
     * Files that have been loaded by this loader.
     * @var array<string>
     */
    protected $_files = array();
    
    /**
     * Load all built-in tasks and application tasks.
     */
    public function loadAll()
    {
        $this->loadBuiltins();
        $this->loadApplication();
    }

    /**
     * Load built-in tasks.
     */
    public function loadBuiltins()
    {
        $path = dirname(__FILE__) . '/BuiltinSet';
        $this->loadDirectory($path);
    }

    /**
     * Load application tasks.
     */
    public function loadApplication()
    {
        $path = MAD_ROOT . '/lib/tasks';
        $this->loadDirectory($path);
    }

    /**
     * Recursively load all PHP files in a task directory.
     */
    public function loadDirectory($path)
    {
        if (! is_dir($path)) { return; }
        
        foreach(new RecursiveIteratorIterator(
                 new RecursiveDirectoryIterator($path)) as $file) {

            if ($file->isFile() && preg_match('/.php$/', $file->getFilename())) {
                $pathname = $file->getPathname();
                $this->_files[] = $pathname;
                require_once $pathname;
            }        
        }
    }

    /**
     * Get files that have been loaded.
     *
     * @return array<string>  Pathnames
     */
    public function getLoadedFiles()
    {
        return $this->_files;
    }

}
