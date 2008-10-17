<?php
/**
 * @category   Mad
 * @package    Support
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * This class offers some additional filesystem tools
 *
 * @category   Mad
 * @package    Support
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Support_FileUtils
{
    /**
     * Recursive copy
     * 
     * cp_r('foo.txt',  'bar.txt') => bar.txt
     * cp_r('foo.txt',  'bar/')    => foo/bar.txt 
     * cp_r('foo/',     'bar/')    => bar/foo
     * 
     * @param   string  $source
     * @param   string  $dest
     */
    public static function cp_r($source, $dest) 
    {
        // file copy
        if (is_file($source)) { 
            if (is_dir($dest)) { $dest = $dest.basename($source); }
            return copy($source, $dest); 
        }

        // directory copy. Ignore .svn dirs
        foreach (new RecursiveIteratorIterator(
                 new RecursiveDirectoryIterator($source)) as $file) {
            $filepath = str_replace($source, "", $file);
            if (strstr($filepath, '.svn')) { continue; } 

            if (!file_exists(dirname("$dest/$filepath"))) {
                mkdir(dirname("$dest/$filepath"), 0755, true);
            }
            copy($file, "$dest/$filepath");
        }
    }

    /**
     * Recursive unlink
     * @param   string  $path
     */
    public static function rm_rf($path)
    {
        if (!file_exists($path)) { return false; }

        // remove files/links
        if (is_file($path) || is_link($path)) { return @unlink($path); }

        // recursively rm
        foreach (scandir($path) as $filename) {
            if ($filename == '.' || $filename == '..') { continue; }
            $file = str_replace('//', '/', $path.'/'.$filename);
            self::rm_rf($file);
        }
        // rm parent dir
        if (!@rmdir($path)) { return false; }
        return true;
    }
}
