<?php
/**
 * @category   Mad
 * @package    Mad_Support
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * Static class with methods to format data
 *
 * @category   Mad
 * @package    Mad_Support
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Support_Inflector
{
    /**
     * @var array   cache inflections performed
     */
    protected static $_inflections = array();


    /**
     * Returns the plural form of the word in the string.
     *
     * Examples
     *   Mad_Support_Inflector::pluralize("post")             #=> "posts"
     *   Mad_Support_Inflector::pluralize("octopus")          #=> "octopi"
     *   Mad_Support_Inflector::pluralize("sheep")            #=> "sheep"
     *   Mad_Support_Inflector::pluralize("words")            #=> "words"
     *   Mad_Support_Inflector::pluralize("the blue mailman") #=> "the blue mailmen"
     *   Mad_Support_Inflector::pluralize("CamelOctopus")     #=> "CamelOctopi"
     */
    public static function pluralize($word)
    {
        if ($result = self::getCache($word, 'pluralize')) { 
            return $result; 
        }

        $pluralRules = array(
            '/(s)tatus$/'             => '\1\2tatuses',
            '/^(ox)$/'                => '\1\2en',      // ox
            '/([m|l])ouse$/'          => '\1ice',       // mouse, louse
            '/(matr|vert|ind)ix|ex$/' => '\1ices',      // matrix, vertex, index
            '/(x|ch|ss|sh)$/'         => '\1es',        // search, switch, fix, box, process, address
            '/([^aeiouy]|qu)y$/'      => '\1ies',       // query, ability, agency
            '/(hive)$/'               => '\1s',         // archive, hive
            '/(?:([^f])fe|([lr])f)$/' => '\1\2ves',     // half, safe, wife
            '/sis$/'                  => 'ses',         // basis, diagnosis
            '/([i])um$/'              => '\1a',         // medium
            '/(p)erson$/'             => '\1eople',     // person, salesperson
            '/(m)an$/'                => '\1en',        // man, woman, spokesman
            '/(c)hild$/'              => '\1hildren',   // child
            '/(buffal|tomat)o$/'      => '\1\2oes',     // buffalo, tomato
            '/(bu)s$/'                => '\1\2ses',     // bus
            '/(alias)/'               => '\1es',         // alias
            '/(octop|vir)us$/'        => '\1i',         // octopus, virus - virus has no defined plural (according to Latin/dictionary.com), but viri is better than viruses/viruss
            '/(ax|cri|test)is$/'      => '\1es',        // axis, crisis
            '/s$/'                    => 's',           // no change (compatibility)
            '/$/'                     => 's'
        );

        foreach ($pluralRules as $rule => $replacement) {
            if (preg_match($rule, $word)) {
                $result = preg_replace($rule, $replacement, $word);
                break;
            }
        }
        $result = !empty($result) ? $result : $word;
        return self::setCache($word, 'pluralize', $result);
    }


    /**
     * The reverse of pluralize, returns the singular form of a word in a string.
     *
     * Examples
     *   Mad_Support_Inflector::singularize("posts")            #=> "post"
     *   Mad_Support_Inflector::singularize("octopi")           #=> "octopus"
     *   Mad_Support_Inflector::singularize("sheep")            #=> "sheep"
     *   Mad_Support_Inflector::singularize("word")             #=> "word"
     *   Mad_Support_Inflector::singularize("the blue mailmen") #=> "the blue mailman"
     *   Mad_Support_Inflector::singularize("CamelOctopi")      #=> "CamelOctopus"
     */
    public static function singularize($word)
    {
        if ($result = self::getCache($word, 'singularize')) { 
            return $result; 
        }

        $singularRules = array(
            '/(s)tatus$/i'          => '\1\2tatus',
            '/(s)tatuses$/'         => '\1\2tatus',
            '/(matr)ices$/'         =>'\1ix',
            '/(vert|ind)ices$/'     => '\1ex',
            '/^(ox)en/'             => '\1',
            '/(alias)es$/'          => '\1',
            '/([octop|vir])i$/'     => '\1us',
            '/(cris|ax|test)es$/'   => '\1is',
            '/(shoe)s$/'            => '\1',
            '/(o)es$/'              => '\1',
            '/(bus)es$/'            => '\1',
            '/([m|l])ice$/'         => '\1ouse',
            '/(x|ch|ss|sh)es$/'     => '\1',
            '/(m)ovies$/'           => '\1\2ovie',
            '/(s)eries$/'           => '\1\2eries',
            '/([^aeiouy]|qu)ies$/'  => '\1y',
            '/([lr])ves$/'          => '\1f',
            '/(tive)s$/'            => '\1',
            '/(hive)s$/'            => '\1',
            '/([^f])ves$/'          => '\1fe',
            '/(^analy)ses$/'        => '\1sis',
            '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/' => '\1\2sis',
            '/([i])a$/'             => '\1um',
            '/(p)eople$/'           => '\1\2erson',
            '/(m)en$/'              => '\1an',
            '/(c)hildren$/'         => '\1\2hild',
            '/(n)ews$/'             => '\1\2ews',
            '/ess$/'                => 'ess',
            '/s$/'                  => ''
        );

        foreach ($singularRules as $rule => $replacement) {
            if (preg_match($rule, $word)) {
                $result = preg_replace($rule, $replacement, $word);
                break;
            }
        }
        $result = !empty($result) ? $result : $word;
        return self::setCache($word, 'singularize', $result);
    }


    /**
     * By default, camelize converts strings to UpperCamelCase. If the argument to camelize
     * is set to ":lower" then camelize produces lowerCamelCase.
     *
     * camelize will also convert '/' to '::' which is useful for converting paths to namespaces
     *
     * Examples
     *   Mad_Support_Inflector::camelize("active_record")                 #=> "ActiveRecord"
     *   Mad_Support_Inflector::camelize("active_record", 'lower')        #=> "activeRecord"
     *   Mad_Support_Inflector::camelize("active_record/errors")          #=> "ActiveRecord/Errors"
     *   Mad_Support_Inflector::camelize("active_record/errors", 'lower') #=> "activeRecord/Errors"
     */
    public static function camelize($lowerCaseAndUnderscoredWord, $firstLetter='upper')
    {
        // check cache
        $word = $lowerCaseAndUnderscoredWord;
        if ($result = self::getCache($word, 'camelize'.$firstLetter)) { 
            return $result; 
        }

        // underscores namespaced
        $namespaced = false;
        if (strtolower($word) != $word && strstr($word, '_')) {
            $namespaced = true;
            $word = str_replace('_', '/', $word);
        }
        // slash namespaced
        if (strstr($word, '/')) {
            $namespaced = true;
            $word = str_replace('/', '/ ', $word);
        }
        $result = self::underscore($word);
        $result = str_replace(' ', '', ucwords(str_replace('_', ' ', $result)));

        // lowercase first letter of each namespace
        if ($firstLetter == 'lower') {
            $parts = array();
            foreach (explode('/', $result) as $part) {
                $parts[] = strtolower($part[0]).substr($part, 1);
            }
            $result = join('/', $parts);
        }
        return self::setCache($word, 'camelize'.$firstLetter, $result);
    }

    /**
     * Capitalizes all the words and replaces some characters in the string to create
     * a nicer looking title. Titleize is meant for creating pretty output. It is not
     * used in the Rails internals.
     *
     * titleize is also aliased as as titlecase
     *
     * Examples
     *   Mad_Support_Inflector::titleize("man from the boondocks") #=> "Man From The Boondocks"
     *   Mad_Support_Inflector::titleize("x-men: the last stand")  #=> "X Men: The Last Stand"
     */
    public static function titleize($word)
    {
        throw new Exception('not implemented yet');
    }


    /**
     * The reverse of +camelize+. Makes an underscored form from the expression in the string.
     *
     * Examples
     *   Mad_Support_Inflector::underscore("ActiveRecord")        #=> "active_record"
     *   Mad_Support_Inflector::underscore("ActiveRecord_Errors") #=> active_record_errors
     */
    public static function underscore($camelCasedWord)
    {
        $word = $camelCasedWord;
        if ($result = self::getCache($word, 'underscore')) { 
            return $result; 
        }
        $result = strtolower(preg_replace('/([a-z])([A-Z])/', "\${1}_\${2}", $word));
        return self::setCache($word, 'underscore', $result);
    }


    /**
     * Replaces underscores with dashes in the string.
     *
     * Example
     *   Mad_Support_Inflector::dasherize("puni_puni") #=> "puni-puni"
     */
    public static function dasherize($underscoredWord)
    {
        if ($result = self::getCache($underscoredWord, 'dasherize')) { 
            return $result; 
        }

        $result = str_replace('_', '-', self::underscore($underscoredWord));
        return self::setCache($underscoredWord, 'dasherize', $result);
    }


    /**
     * Capitalizes the first word and turns underscores into spaces and strips _id.
     * Like titleize, this is meant for creating pretty output.
     *
     * Examples
     *   Mad_Support_Inflector::humanize("employee_salary") #=> "Employee salary"
     *   Mad_Support_Inflector::humanize("author_id")       #=> "Author"
     */
    public static function humanize($lowerCaseAndUnderscoredWord)
    {
        $word = $lowerCaseAndUnderscoredWord;
        if ($result = self::getCache($word, 'humanize')) { 
            return $result; 
        }

        $result = ucfirst(str_replace('_', ' ', self::underscore($word)));
        if (substr($result, -3, 3) == ' id') {
            $result = str_replace(' id', '', $result);
        }
        return self::setCache($word, 'humanize', $result);
    }


    /**
     * Removes the module part from the expression in the string
     *
     * Examples
     *   Mad_Support_Inflector::demodulize("Fax_Job") #=> "Job"
     *   Mad_Support_Inflector::demodulize("User")    #=> "User"
     */
    public static function demodulize($classNameInModule)
    {
        $result = explode('_', $classNameInModule);
        return array_pop($result);
    }

    /**
     * Create the name of a table like Rails does for models to table names. This method
     * uses the pluralize method on the last word in the string.
     *
     * Examples
     *   Mad_Support_Inflector::tableize("RawScaledScorer") #=> "raw_scaled_scorers"
     *   Mad_Support_Inflector::tableize("egg_and_ham")     #=> "egg_and_hams"
     *   Mad_Support_Inflector::tableize("fancyCategory")   #=> "fancy_categories"
     */
    public static function tableize($className)
    {
        if ($result = self::getCache($className, 'tableize')) { 
            return $result; 
        }

        $result = self::pluralize(self::underscore($className));
        $result = str_replace('/', '_', $result);
        return self::setCache($className, 'tableize', $result);
    }

    /**
     * Create a class name from a table name like Rails does for table names to models.
     * Note that this returns a string and not a Class. (To convert to an actual class
     * follow classify with constantize.)
     *
     * Examples
     *   Mad_Support_Inflector::classify("egg_and_hams") #=> "EggAndHam"
     *   Mad_Support_Inflector::classify("post")         #=> "Post"
     */
    public static function classify($tableName)
    {
        if ($result = self::getCache($tableName, 'classify')) { 
            return $result; 
        }
        $result = self::camelize(self::singularize($tableName));

        // classes use underscores instead of slashes for namespaces
        $result = str_replace('/', '_', $result);
        return self::setCache($tableName, 'classify', $result);
    }

    /**
     * Creates a foreign key name from a class name.
     * +separate_class_name_and_id_with_underscore+ sets whether
     * the method should put '_' between the name and 'id'.
     *
     * Examples
     *   Mad_Support_Inflector::foreignKey("Message")        #=> "message_id"
     *   Mad_Support_Inflector::foreignKey("Message", false) #=> "messageid"
     *   Mad_Support_Inflector::foreignKey("Fax_Job")        #=> "fax_job_id"
     */
    public static function foreignKey($className, $separateClassNameAndIdWithUnderscore=true)
    {
        throw new Exception('not implemented yet');
    }

    /**
     * Ordinalize turns a number into an ordinal string used to denote the
     * position in an ordered sequence such as 1st, 2nd, 3rd, 4th.
     *
     * Examples
     *   Mad_Support_Inflector::ordinalize(1)     # => "1st"
     *   Mad_Support_Inflector::ordinalize(2)     # => "2nd"
     *   Mad_Support_Inflector::ordinalize(1002)  # => "1002nd"
     *   Mad_Support_Inflector::ordinalize(1003)  # => "1003rd"
     */
    public static function ordinalize($number)
    {
        throw new Exception('not implemented yet');
    }


    /*##########################################################################
    # Store the results of the inflections to increase performance
    ##########################################################################*/

    /**
     * Stale the cache of inflection data
     */
    public static function clearCache()
    {
        self::$_inflections = array();
    }

    /**
     * Retrieve an inflection from the cache. Inflections can be processor intensive
     * especially with preg_match(). We want to cut down on as many preg_match as possible
     * by caching all inflections done in a hash.
     *
     * @param   string  $value
     * @param   string  $rule
     * @return  string
     */
    public static function getCache($value, $rule)
    {
        if (isset(self::$_inflections[$value.$rule])) {
            return self::$_inflections[$value.$rule];
        } else {
            return false;
        }
    }

    /**
     * Save an inflection to the cache
     * 
     * @param   string  $value
     * @param   string  $rule
     * @param   string  $inflection
     */
    public static function setCache($value, $rule, $inflection)
    {
        self::$_inflections[$value.$rule] = $inflection;
        return $inflection;
    }
}
