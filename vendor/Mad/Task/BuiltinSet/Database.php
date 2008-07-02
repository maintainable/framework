<?php
/**
 * @category   Mad
 * @package    Mad_Task
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */

/**
 * Built-in framework tasks for database operations.
 *
 * @category   Mad
 * @package    Mad_Task
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_Task_BuiltinSet_Database extends Mad_Task_Set
{
    /**
     * Migrate the database through scripts in db/migrate.
     */
    public function db_migrate()
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

}
