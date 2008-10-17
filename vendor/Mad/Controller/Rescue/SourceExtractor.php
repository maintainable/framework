<?php
/**
 * @category   Mad
 * @package    Mad_Controller
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * Extract source code to help examine errors.
 *
 * @category   Mad
 * @package    Mad_Controller
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Controller_Rescue_SourceExtractor
{
    /**
     * Given an exception, attempt to read the source code
     * and return a snippet.
     *
     * @param  Exception  $exception    Exception
     * @return stdClass                 stdClass(->line, ->source)
     */
    public function extractSourceFromException($exception)
    {
        $line = $exception->getLine();
        if (empty($line)) { return false; }

        $source = @file($exception->getFile());
        if (empty($source)) { return false; }
        
        $source = $this->linesAround($source, $line);
        $source = $this->convertTabsToSpaces($source);
        $source = $this->stripWhitespace($source);

        return (object)array('line' => $line, 'source' => $source);
    }

    /**
     * Given an array of $lines, start at $center and return
     * lines $above and $below.  These indexes start at 1, i.e.
     * the source is numbered starting at line 1 and not line 0.
     *
     * Returns an array where each key is a line number and
     * each value a line of source code.
     *
     * @param  array    $lines   All lines of source code
     * @param  integer  $center  Center line number (1..)
     * @param  integer  $above   Number of lines to extract above center
     * @param  integer  $below   Number of lines to extract below center
     * @return array             Array of extracted lines
     */
    public function linesAround($lines, $center, $above=3, $below=3)
    {
        $extracted = array();
        
        // extract source at error line and above
        $lbound = ($center - $above) -2;
        for ($i=$center; $i>0 && $i>$lbound; $i--) {
            $extracted[$i+1] = $lines[$i];
        }

        // extract source below error line
        $ubound = sizeof($lines) -1; 
        if ($center+$below <= $ubound) { $ubound = $center+$below; }
        
        for ($i=$center; $i<$ubound; $i++) {
            $extracted[$i+1] = $lines[$i];
        }

        // order lines of extracted source
        ksort($extracted);
        return $extracted;
    }

    /**
     * Given an array of $lines, replace any tab characters
     * with spaces computed from $tabstop.
     *
     * @param  array  $lines    Lines possibly containing tabs
     * @param  array  $tabstop  Tab stop
     * @return array            Lines with tabs replaced by spaces
     */
    public function convertTabsToSpaces($lines, $tabstop=4)
    {
        foreach ($lines as &$line) {
            while (($pos = strpos($line, "\t")) !== false) {
                $line = substr($line, 0, $pos)
                      . str_repeat(' ', $tabstop - $pos % $tabstop)
                      . substr($line, $pos +1);
            }
        }
        return $lines;
    }

    /**
     * Given an array of $lines, strip only the leading spaces
     * that are common to all lines so indentation is preserved.
     *
     * Also strip any trailing whitespace for all lines.
     *
     * @param  array  $lines    Lines with leading/trailing whitespace
     * @return array            Stripped lines
     */
    public function stripWhitespace($lines) {
        $leaders = array();
        foreach ($lines as &$l) {
            // right-trim line 
            $l = rtrim($l);
            if (! strlen($l)) { 
                continue; // skip blank line
            }

            // count leading spaces of line
            if (! preg_match('/^[ ]*/', $l, $matches)) {
                $leaders[] = 0;
                break;
            } else {
                $leaders[] = strlen($matches[0]);
            }
        }

        // strip leading spaces common to all lines
        $leading = min($leaders);
        foreach ($lines as &$l) { 
            $l = substr($l, $leading); 
        }
        
        return $lines;
    }
    
}