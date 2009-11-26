<?php
/**
 * Class to generate the default application directory layout
 *
 * @category   Mad
 * @package    Mad_Script
 * @copyright  (c) 2009 Philipp Gildein <rmbl@openspeak-project.org>
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * Class to generate the default application directory layout
 *
 * @category   Mad
 * @package    Mad_Script
 * @copyright  (c) 2009 Philipp Gildein <rmbl@openspeak-project.org>
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Script_CreateApp extends Mad_Script_Base
{
    /**
     * Option to overwrite All files when getting input from user
     * @var boolean
     */
    protected $_overwriteAll = false;

    /**
     * The directory in which to put all files/directories
     * @var string
     */
    protected $_dir;

    /**
     * Take the array of arguments given
     * @param   array   $args
     */
    public function __construct($args)
    {
        $filename = array_shift($args);
        $name     = !empty($args) ? array_shift($args) : null;

        if ($name)
          $this->_createApplication($name);
        else
          $this->_displayHelp();
    }

    /**
     * Create all necessary files/directories
     * @param   string  $name
     */
    private function _createApplication($name)
    {
        $dir = getcwd();
        if (!$dir)
          throw Exception("Unable to get working directory");

        // create application directory
        $this->_dir = $dir;
        $this->_createDir($name);
        $this->_dir .= '/' . $name;

        // create directories
        $this->_createDir('/app');
        $this->_createDir('/app/controllers');
        $this->_createDir('/app/models');
        $this->_createDir('/app/helpers');
        $this->_createDir('/app/views');
        $this->_createDir('/app/views/layouts');
        $this->_createDir('/config');
        $this->_createDir('/config/environments');
        $this->_createDir('/db');
        $this->_createDir('/db/migrate');
        $this->_createDir('/lib/tasks');
        $this->_createDir('/log');
        $this->_createDir('/public');
        $this->_createDir('/script');
        $this->_createDir('/test');
        $this->_createDir('/test/fixtures');
        $this->_createDir('/test/functional');
        $this->_createDir('/test/unit');
        $this->_createDir('/vendor');

        // populate directories
        $this->_copy('/Rakefile', '/.');
        $this->_copy('/app/controllers/ApplicationController.php', '/app/controllers');
        $this->_copy('/app/helpers/ApplicationHelper.php', '/app/helpers');
        $this->_copy('/config', '/config');
        $this->_copy('/public/_htaccess', '/public/.htaccess');
        $this->_copy('/public/index.php', '/public');
        $this->_copy('/script/generate', '/script');
        $this->_copy('/script/task', '/script');
        $this->_copy('/test/AllTests.php', '/test');
        $this->_copy('/vendor', '/vendor');
    }

    /**
     * Create directories on the filesystem
     * @param   string  $dir
     */
    private function _createDir($dir)
    {
        if (file_exists($this->_dir . $dir)) {
            $this->_print("      exists  $dir");
        } else {
            mkdir($this->_dir . $dir, 0777, true);
            $this->_print("      create  $dir");
        }
    }

    /**
     * Copy a file or directory from one point to another
     * @param   string  $source
     * @param   string  $dest
     */
    private function _copy($source, $dest)
    {
        // prepend absolute paths
        $from = MAD_ROOT . $source;
        $to   = $this->_dir . $dest;

        if (is_file($from)) {
            if (is_dir($to)) {
                // append slashes if needed
                if ($to[strlen($to)-1] != '/')
                    $to .= '/'; 
                $to .= basename($from); 
            }

            // copy the file
            copy($from, $to);
            chmod($to, fileperms($from));
            $this->_print("      create  $source");
        } else if (is_dir($from)) {
            if(!is_dir($to))
                $this->_createDir($dest);

            // append slashes if needed
            if ($from[strlen($from)-1] != '/')
                $from .= '/'; 
            if ($to[strlen($to)-1]!='/')
                $to .= '/';

            # find all elements in the source directory
            $dirHandle = opendir($from);
            while ($file = readdir($dirHandle)) {
                // and copy them if they're valid files/directories
                if($file != '.' && $file != '..')
                    $this->_copy($source . '/' . $file, $dest . '/' . $file); 
            }
            closedir($dirHandle);
        }
    }

    /*##########################################################################
    # Utility methods
    ##########################################################################*/

    /**
     * Display help guidelines
     */
    private function _displayHelp()
    {
        $msg =
          "\tUsage:                                                                     \n".
          "\t 1. Generate application directory in current working dir                  \n".
          "\t    createapp #Applicationname                                             \n".
          "\t     eg: ./script/createapp exampleapp                                     \n".
          "\t                                                                           \n".
          "\t 2. This help.                                                             \n".
          "\t     ./script/createapp                                                    \n".
          "\n";
        $this->_exit($msg);
    }

}
