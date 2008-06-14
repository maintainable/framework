<?php
/**
 * @category   Mad
 * @package    Mad_Controller
 * @subpackage Response
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */

/**
 * Represents an HTTP response to the user.
 *
 * @category   Mad
 * @package    Mad_Controller
 * @subpackage Response
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_Controller_Mime_Type
{   
    public $symbol;
    public $synonyms;
    public $string;
    
    public static $set             = array();
    public static $lookup          = array();
    public static $extensionLookup = array();
    public static $registered      = false;
    
    public function __construct($string, $symbol = null, $synonyms = array())
    {
        $this->string   = $string;
        $this->symbol   = $symbol;
        $this->synonyms = $synonyms;
    }
    
    public function __toString()
    {
        return $this->symbol;
    }
    
    public static function lookup($string)
    {
        if (!empty(self::$lookup[$string])) {
            return self::$lookup[$string];
        } else {
            return null;
        }
    }
    
    public static function lookupByExtension($ext)
    {
        $look = var_export(self::$extensionLookup['xml'], true);

        if (!empty(self::$extensionLookup[$ext])) {
            return self::$extensionLookup[$ext];
        } else {
            return null;
        }
    }

    public static function register($string, $symbol, $synonyms = array(), $extSynonyms = array())
    {
        $type = new Mad_Controller_Mime_Type($string, $symbol, $synonyms);
        self::$set[] = $type;

        // add lookup strings
        foreach (array_merge((array)$string, $synonyms) as $string) {
            self::$lookup[$string] = $type;
        }

        // add extesnsion lookups
        foreach (array_merge((array)$symbol, $extSynonyms) as $ext) {
            self::$extensionLookup[$ext] = $type;
        }
    }
    
    /**
     * @todo - actually parse the header. This is simply mocked out
     * with common types for now
     */
    public static function parse($acceptHeader)
    {
        $types = array();

        if (strstr($acceptHeader, 'text/javascript')) {
            if (isset(self::$extensionLookup['js'])) {
                $types[] = self::$extensionLookup['js'];
            }
        
        } elseif (strstr($acceptHeader, 'text/html')) {
            if (isset(self::$extensionLookup['html'])) {
                $types[] = self::$extensionLookup['html'];
            }

        } elseif (strstr($acceptHeader, 'text/xml')) {
            if (isset(self::$extensionLookup['xml'])) {
                $types[] = self::$extensionLookup['xml'];
            }
        }
        return $types;
    }

    // Register mime types
    // @todo - move this elsewhere?    
    public static function registerTypes()
    {
        if (!self::$registered) {
            Mad_Controller_Mime_Type::register("*/*",             'all');
            Mad_Controller_Mime_Type::register("text/plain",      'text', array(), array('txt'));
            Mad_Controller_Mime_Type::register("text/html",       'html', array('application/xhtml+xml'), array('xhtml'));
            Mad_Controller_Mime_Type::register("text/javascript", 'js',   array('application/javascript', 'application/x-javascript'), array('xhtml'));
            Mad_Controller_Mime_Type::register("application/xml", 'xml',  array('text/xml', 'application/x-xml'));        
            self::$registered = true;
        }
    }
}
