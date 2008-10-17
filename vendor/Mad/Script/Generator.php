<?php
/**
 * Class to generate directories and code stubs for common class skeletons
 *
 * @category   Mad
 * @package    Mad_Script
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * Class to generate directories and code stubs for common class skeletons
 *
 * @category   Mad
 * @package    Mad_Script
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Script_Generator extends Mad_Script_Base
{
    /**
     * Template to parse skeleton code
     * @var obejct  {@link TemplatePhplib}
     */
    protected $_tpl;

    /**
     * Argument list passed in from the command line
     * @var array
     */
    protected $_args;

    /**
     * The author of the packages (who is running this script)
     * @var string
     */
    protected $_author;

    /**
     * Option to overwrite All files when getting input from user
     * @var boolean
     */
    protected $_overwriteAll = false;


    /*##########################################################################
    # Construct
    ##########################################################################*/

    /**
     * Take the array of arguments given
     * @param   array   $args
     */
    public function __construct($args)
    {
        $this->_tpl = new Mad_View_Base();
        $this->_tpl->addPath('vendor/Mad/Script/templates');

        $filename = array_shift($args);
        $action   = !empty($args) ? array_shift($args) : null;
        $this->_args = $args;

        // generate model stubs
        if (strtolower($action) == 'model') {
            $this->_generateModelStubs();

        // generate migration stubs
        } elseif (strtolower($action) == 'migration') {
            $name = !empty($this->_args) ? array_shift($this->_args) : null;
            $migration = Mad_Support_Inflector::underscore($name);

            $this->_generateMigrationStub($migration);

        // generate controller stubs
        } elseif (strtolower($action) == 'controller') {
            $this->_generateControllerStubs();

        // generate helper stubs
        } elseif (strtolower($action) == 'helper') {
            $this->_generateHelperStubs();

        } elseif (strtolower($action) == 'mailer') {
            $this->_generateMailerStubs();

        // invalid option - show help page
        } else {
            $this->_displayHelp();
        }
    }


    /*##########################################################################
    # Generators
    ##########################################################################*/

    /**
     * Generate model class stubs
     */
    private function _generateModelStubs()
    {
        $name = !empty($this->_args) ? array_shift($this->_args) : null;
        if (!$name) {
            $this->_exit("You did not specify the name of the Model to generate");
        }

        // CREATE FILES
        $modelFile = Mad_Support_Inflector::camelize($name);
        $modelName = Mad_Support_Inflector::classify($name);
        $tableName = Mad_Support_Inflector::tableize($name);

        // make namespace dir
        if (strstr($modelFile, '/')) {
            $this->_createDir(MAD_ROOT.'/app/models/'.dirname($modelFile).'/');
        }

        // template files & common vars
        $this->_tpl->author = $this->_author;
        $this->_tpl->className = $modelName;
        $this->_tpl->tableName = $tableName;

        // create Model file snippit
        $content = $this->_tpl->render('model.php');
        $this->_createFile(MAD_ROOT."/app/models/{$modelFile}.php", $content);

        // create Unit Test stub
        // make namespace dir
        if (strstr($modelFile, '/')) {
            $this->_createDir(MAD_ROOT.'/test/unit/'.dirname($modelFile).'/');
        }
        $this->_tpl->classFile = "models/{$modelName}.php";
        $this->_tpl->package = 'Models';
        $content = $this->_tpl->render('unit_test.php');
        $this->_createFile(MAD_ROOT."/test/unit/{$modelFile}Test.php", $content);

        // create Migration stub
        $this->_generateMigrationStub("create_$tableName");

        // create fixture stub
        $this->_createFile(MAD_ROOT."/test/fixtures/{$tableName}.yml", null);
    }

    /**
     * Generate migration class stubs
     */
    private function _generateMigrationStub($migrationName)
    {
        // create Migration stub
        $this->_tpl->migrationName = Mad_Support_Inflector::camelize($migrationName);
        $content = $this->_tpl->render('migration.php');

        // Find next migration version
        $versions = array();
        foreach (glob(MAD_ROOT."/db/migrate/[0-9]*_*.php") as $file) {
            preg_match_all('/([0-9]+)_([_a-z0-9]*).php/', $file, $matches);
            $versions[] = (int)$matches[1][0];
        }
        natsort($versions);
        $versions = array_reverse($versions);
        $lastVersion = !empty($versions) ? $versions[0] : 0;
        $lastVersion = str_pad($lastVersion, 3, "0", STR_PAD_LEFT);

        // we already made this migration 
        $migrationFile = MAD_ROOT."/db/migrate/{$lastVersion}_{$migrationName}.php";
        if (!file_exists($migrationFile)) {
            $nextVersion = !empty($versions) ? $versions[0] + 1 : 1;
            $nextVersion = str_pad($nextVersion, 3, "0", STR_PAD_LEFT);
            $migrationFile = MAD_ROOT."/db/migrate/{$nextVersion}_{$migrationName}.php";
        }

        $this->_createFile($migrationFile, $content);
    }

    /**
     * Generate controller class stubs
     */
    private function _generateControllerStubs()
    {
        $name = !empty($this->_args) ? array_shift($this->_args) : null;
        if (!$name) {
            $this->_exit("You did not specify the name of the Controller to generate");
        }
        // strip off controller if it's there
        $name = str_replace('Controller', '', $name);


        // CREATE DIRECTORIES
        $this->_createDir(MAD_ROOT.'/app/views/'.Mad_Support_Inflector::camelize($name).'/');

        // CREATE DIRECTORIES
        $this->_createDir(MAD_ROOT.'/test/functional/');

        // CREATE FILES
        $class = Mad_Support_Inflector::camelize($name);
        $contrName  = $class.'Controller';
        $helperName = $class.'Helper';

        // template files & common vars
        $this->_tpl->author     = $this->_author;
        $this->_tpl->className  = $contrName;
        $this->_tpl->helperName = $helperName;


        // create Controller stub
        $content = $this->_tpl->render('controller.php');
        $this->_createFile(MAD_ROOT."/app/controllers/{$contrName}.php", $content);

        // create Helper stub
        $content = $this->_tpl->render('helper.php');
        $this->_createFile(MAD_ROOT."/app/helpers/{$helperName}.php", $content);

        // create Functional Test stub
        $this->_tpl->classFile = "controllers/{$contrName}.php";
        $this->_tpl->package = 'Controllers';
        $content = $this->_tpl->render('functional_test.php');
        $this->_createFile(MAD_ROOT."/test/functional/{$contrName}Test.php", $content);
    }

    /**
     * Generate helper class stubs
     */
    private function _generateHelperStubs()
    {
        $name = !empty($this->_args) ? array_shift($this->_args) : null;
        if (!$name) {
            $this->_exit("You did not specify the name of the Helper to generate");
        }
        // strip off controller if it's there
        $name = str_replace('Helper', '', $name);

        // CREATE FILES
        $class = Mad_Support_Inflector::classify($name);
        $helperName = $class.'Helper';

        // template files & common vars
        $this->_tpl->author = $this->_author;
        $this->_tpl->className = $class;
        $this->_tpl->helperName = $helperName;

        // create Helper stub
        $content = $this->_tpl->render('helper.php');
        $this->_createFile(MAD_ROOT."/app/helpers/{$helperName}.php", $content);
    }

    /**
     * Generate mailer class stubs
     */
    private function _generateMailerStubs()
    {
        $name = !empty($this->_args) ? array_shift($this->_args) : null;
        if (!$name) {
            $this->_exit("You did not specify the name of the Mailer to generate");
        }
        $name = str_replace('Mailer', '', $name);
        $mailerName = Mad_Support_Inflector::camelize($name).'Mailer';

        $this->_tpl->mailerName = $mailerName;
        $content = $this->_tpl->render('mailer.php');
        $this->_createFile(MAD_ROOT."/app/models/{$mailerName}.php", $content);
        
        // CREATE DIRECTORIES
        $this->_createDir(MAD_ROOT.'/app/views/'.$mailerName);
    }

    /*##########################################################################
    # File/Directory creation methods
    ##########################################################################*/

    /**
     * Create directories on the filesystem
     * @param   string  $dir
     */
    private function _createDir($dir)
    {
        if (file_exists($dir)) {
            $this->_print("      exists  $dir");
        } else {
            mkdir($dir, 0777, true);
            $this->_print("      create  $dir");
        }
    }

    /**
     * Create a new file, or overwrite an existing file
     * @param   string  $filepath
     * @param   string  $content
     * @todo mkdir() temporary hack
     */
    private function _createFile($filepath, $content)
    {
        // File exists. Check for diff && prompt user for action
        if (file_exists($filepath)) {
            if (file_get_contents($filepath) == $content) {
                $this->_print("   identical  $filepath");
                return;
            }
            $write = $this->_overwriteAll ? 'Y' : $this->_prompt("overwrite $filepath? [Ynqa] ");

            // overwrite all files
            if ($write == 'a') {
                $this->_overwriteAll = true;
                $write = 'Y';
            }
            // quit script
            if ($write == 'q') {
                $this->_exit();
            // don't overwrite
            } elseif ($write == 'n') {
                $this->_print("        skip    $filepath");
            // overwrite
            } elseif ($write == 'Y') {
                file_put_contents($filepath, $content);
                $this->_print("       force  $filepath");
            }

        // File is new
        } else {
            file_put_contents($filepath, $content);
            $this->_print("      create  $filepath");
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
          "\t 1. Generate controller class                                              \n".
          "\t    generate controller #ControllerName                                    \n".
          "\t      eg:                                                                  \n".
          "\t       ./script/generate controller Browse                                 \n".
          "\t                                                                           \n".
          "\t 2. Generate model class                                                   \n".
          "\t     generate model #ModelName                                             \n".
          "\t       eg:                                                                 \n".
          "\t       ./script/generate model Binder briefcases                           \n".
          "\t                                                                           \n".
          "\t 3. This help.                                                             \n".
          "\t     ./script/generate                                                     \n".
          "\n";
        $this->_exit($msg);
    }
}
