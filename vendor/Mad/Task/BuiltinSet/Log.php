<?php
/**
 * @category   Mad
 * @package    Mad_Task
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * Built-in framework tasks for log files.
 *
 * @category   Mad
 * @package    Mad_Task
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Task_BuiltinSet_Log extends Mad_Task_Set
{
    /**
     * Truncates all *.log files in log/ to zero bytes
     */
    public function log_clear()
    {
        foreach (new DirectoryIterator(MAD_ROOT . '/log') as $file) {
            if ($file->isFile() && preg_match('/\.log$/', $file->getFilename())) {
                $f = fopen($file->getPathName(), 'w');
                fclose($f);
            }
        }
    }

}
