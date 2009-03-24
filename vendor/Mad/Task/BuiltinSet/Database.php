<?php
/**
 * @category   Mad
 * @package    Mad_Task
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * Built-in framework tasks for database operations.
 *
 * @category   Mad
 * @package    Mad_Task
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Task_BuiltinSet_Database extends Mad_Task_Set
{
    /**
     * Migrate the database through scripts in db/migrate.
     */
    public function db_migrate($ver = null)
    {
        try {
            $script  = array_shift($GLOBALS['argv']);
            $task    = array_shift($GLOBALS['argv']);
            $version = array_shift($GLOBALS['argv']);
            $ver = preg_match('/VERSION=(\d*)/', $version, $match) ? $match[1] : null;

            Mad_Model_Migration_Migrator::migrate(MAD_ROOT."/db/migrate/", $ver);

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Resets your database using your migrations for the current environment
     */
    public function db_reset()
    {
        $path = MAD_ROOT . '/db/migrate/';
        Mad_Model_Migration_Migrator::down($path, 0);
        Mad_Model_Migration_Migrator::up($path);
    }

}
