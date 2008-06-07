<?php
/**
 * @category   Mad
 * @package    Mad_Controller
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */

/**
 * Extract source code to help examine errors.
 *
 * @category   Mad
 * @package    Mad_Controller
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_Controller_Rescue_SourceExtractor
{
    /**
     * Given an exception, attempt to read the source code
     * and return a snippet.
     */
    public function extractSourceFromException($exception)
    {
        $line = $exception->getLine();
        if (empty($line)) { return false; }

        $source = @file($exception->getFile());
        if (empty($source)) { return false; }
        
        $source = $this->linesAround($source, $line);
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
     * Given an array of $lines, strip only the leading spaces
     * that are common to all lines so indentation is preserved.
     *
     * Also strip any trailing whitespace for all lines.
     *
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