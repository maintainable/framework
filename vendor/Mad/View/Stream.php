<?php
/**
 * Stream wrapper to convert markup of mostly-PHP templates 
 * into PHP prior to include().
 *
 * @category   Mad
 * @package    Mad_View
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * Stream wrapper to convert markup of mostly-PHP templates 
 * into PHP prior to include().
 *
 * Based in large part on the example at
 * http://www.php.net/manual/en/function.stream-wrapper-register.php
 *
 * @category   Mad
 * @package    Mad_View
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_View_Stream
{
    /**
     * Force rewriting short tags?  Primarily for testing.
     * @var boolean
     */
    public $forceShortTagRewrite = false;

    /**
     * Current stream position.
     * @var int
     */
    private $pos = 0;

    /**
     * Data for streaming.
     * @var string
     */
    private $data;

    /**
     * Has the data to stream been processed?
     * @var boolean
     */
    private $processed = false;

    /**
     * Stream stats.
     * @var array
     */
    private $stat;


    /**
     * Install this stream wrapper.
     */
    public static function install()
    {
        $wrappers = stream_get_wrappers();

        if (! in_array('madview', $wrappers)) {
            stream_wrapper_register('madview', 'Mad_View_Stream');
        }
    }

    /**
     * Opens the script file and converts markup.
     */
    public function stream_open($path, $mode, $options, &$opened_path)
    {
        // get the view script source
        $path = str_replace('madview://', '', $path);
        $this->data = file_get_contents($path);
        $this->processed = false;

        /**
         * If reading the file failed, update our local stat store
         * to reflect the real stat of the file, then return on failure
         */
        if ($this->data === false) {
            $this->stat = stat($path);
            return false;
        }
        
        /**
         * file_get_contents() won't update PHP's stat cache, so performing
         * another stat() on it will hit the filesystem again.  Since the file
         * has been successfully read, avoid this and just fake the stat
         * so include() is happy.
         */
        $this->stat = array('mode' => 0100777, 'size' => strlen($this->data));
        return true;
    }

    /**
     * Process the $this->data before returning it.
     */
    private function _process()
    {
        /**
         * If short open tags is off, convert <? ?> to long-form <?php ?>
         * and <?= ?> to long-form <?php echo ?>.
         * 
         * Does not covert ASP-style <%= tags.
         */
        if ($this->forceShortTagRewrite || (! ini_get('short_open_tag'))) {
	        $find    = array('/\<\? (.*?)(\?\>){1}?/s',
	                         '/\<\?\= (.*?)(\?\>){1}?/s');
	        $replace = array('<?php $1?>',
	                         '<?php echo $1?>');
            $this->data = preg_replace($find, $replace, $this->data);
        }

        // Convert @$this->varName to htmlentities($this->varName, ENT_QUOTES, 'utf-8')
        if (strpos($this->data, '@') !== false) {
            $find    = '/@\$([a-z0-9_\[\]\->\']*)/i';
            $replace = 'htmlentities($$1, ENT_QUOTES, \'utf-8\')';
            $this->data = preg_replace($find, $replace, $this->data);
        }

        /* Convert ['foo' => 'bar'] to array('foo' => 'bar'). Also works for
         * nested arrays: ['foo' => ['bar' => 'baz']].
         */
        if (strpos($this->data, '[') !== false) {
            $find    = '/\[([^]]+?=>[^]]+?)\]{1}?/s';
            $replace = 'array($1)';
            $count = 0;
            do {
                $this->data = preg_replace($find, $replace, $this->data, -1, $count);
            } while ($count);
        }
        
        $this->processed = true;
    }

    /**
     * Reads from the stream.
     */
    public function stream_read($count)
    {
        if (! $this->processed) { $this->_process(); }
                
        $ret = substr($this->data, $this->pos, $count);
        $this->pos += strlen($ret);
        return $ret;
    }

    /**
     * Tells the current position in the stream.
     */
    public function stream_tell()
    {
        return $this->pos;
    }

    /**
     * Tells if we are at the end of the stream.
     */
    public function stream_eof()
    {
        return $this->pos >= strlen($this->data);
    }

    /**
     * Stream statistics.
     */
    public function stream_stat()
    {
        return $this->stat;
    }

    /**
     * Seek to a specific point in the stream.
     */
    public function stream_seek($offset, $whence)
    {
        if (! $this->processed) { $this->_process(); }
      
        switch ($whence) {
            case SEEK_SET:
                if ($offset < strlen($this->data) && $offset >= 0) {
                    $this->pos = $offset;
                    return true;
                } else {
                    return false;
                }
                break;

            case SEEK_CUR:
                if ($offset >= 0) {
                    $this->pos += $offset;
                    return true;
                } else {
                    return false;
                }
                break;

            case SEEK_END:
                if (strlen($this->data) + $offset >= 0) {
                    $this->pos = strlen($this->data) + $offset;
                    return true;
                } else {
                    return false;
                }
                break;

            default:
                return false;
        }
    }

    /**
     * Stat url
     */
    public function url_stat($path, $flags)
    {
    	return 0;
    }
}
