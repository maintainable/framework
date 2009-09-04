<?php
/**
 * @category   Mad
 * @package    Mad_Model
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * @category   Mad
 * @package    Mad_Model
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Model_Stream
{
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

        if (! in_array('madmodel', $wrappers)) {
            stream_wrapper_register('madmodel', 'Mad_Model_Stream');
        }
    }

    /**
     * Opens the script file and converts markup.
     */
    public function stream_open($path, $mode, $options, &$opened_path)
    {
        // get the model script source
        $path = str_replace('madmodel://', '', $path);
        $this->data = file_get_contents($path);

        /**
         * If reading the file failed, update our local stat store
         * to reflect the real stat of the file, then return on failure
         */
        if ($this->data === false) {
            $this->stat = stat($path);
            return false;
        }

        /**
         * Add the static methods but only if this model file extends Mad_Model_Base.  
         */ 
        if (preg_match('/extends\s+mad_model_base\s*{/i', $this->data)) {
            $this->_addStaticMethods();
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
     * Reads from the stream.
     */
    public function stream_read($count)
    {
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

    /**
     * Add the static methods to the model. 
     */
    protected function _addStaticMethods()
    {
        $replace =
'$1 $2 $3
    public static function className()
    {
        return \'$2\';
    }
    public static function find($type, $options=array(), $bindVars=null)
    {
        return parent::find($type, $options, $bindVars);
    }
    public static function first($options=array(), $bindVars=null)
    {
        return parent::first($options, $bindVars);
    }
    public static function count($options=array(), $bindVars=null)
    {
        return parent::count($options, $bindVars);
    }
    public static function findBySql($type, $sql, $bindVars=null)
    {
        return parent::findBySql($type, $sql, $bindVars);
    }
    public static function countBySql($sql, $bindVars=null)
    {
        return parent::countBySql($sql, $bindVars);
    }
    public static function paginate($options=array(), $bindVars=null)
    {
        return parent::paginate($options, $bindVars);
    }
    public static function exists($id)
    {
        return parent::exists($id);
    }
    public static function create($attributes)
    {
        return parent::create($attributes);
    }
    public static function update($id, $attributes=null)
    {
        return parent::update($id, $attributes);
    }
    public static function updateAll($set, $conditions=null, $bindVars=null)
    {
        return parent::updateAll($set, $conditions, $bindVars);
    }
    public static function delete($id)
    {
        return parent::delete($id);
    }
    public static function deleteAll($conditions=null, $bindVars=null)
    {
        return parent::deleteAll($conditions, $bindVars);
    } 
$4';
        $pattern = "/(.*class) (.*) (extends.*)(}[^}]+?)$/s";
        $this->data = preg_replace($pattern, $replace, $this->data);
    }
}