<?php
/**
 * @category   Mad
 * @package    Mad_Controller
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */

/**
 * Render an error page when an exception occurs.
 *
 * @category   Mad
 * @package    Mad_Controller
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_Controller_Rescue_Renderer
{
    /**
     * @var Mad_View_Base
     */
    protected $_view = null;

    /**
     * @var Mad_Controller_Rescue_SourceExtractor
     */
    protected $_extractor = null;

    /**
     * Constructor.
     *
     * @var  Mad_View_Base  $view
     */
    public function __construct($view = null, $extractor = null)
    {
        if ($view === null) {
            $view = new Mad_View_Base();
            $view->addPath( dirname(__FILE__) );
        }
        $this->_view = $view;

        if ($extractor === null) {
            $extractor = new Mad_Controller_Rescue_SourceExtractor();
        }
        $this->_extractor = $extractor;
    }

    /**
     * Render an HTML error page from an exception.
     *
     * @param  Exception                     $exception
     * @param  Mad_Controller_Request_Http   $request
     * @param  Mad_Controller_Response_Http  $response
     */
    public function render($exception, $request, $response)
    {
        // If there is anything leftover in the output buffer, such
        // as interrupted template rendering, destroy it.
        while (ob_get_level()) { ob_get_clean(); }

        // title
        if ($exception instanceof Mad_Support_Exception) {
            $title = $exception->getTitle();
        } else {
            $title = get_class($exception);
        }

        // message
        if (! strlen($message = $exception->getMessage())) {
            $message = "<no message>";
        }

        // assignments
        $this->_view->title      = $title;
        $this->_view->message    = $message;
        $this->_view->exception  = $exception;
        $this->_view->trace      = $this->formatTrace($exception);
        $this->_view->request    = $request;
        $this->_view->response   = $response;
        $this->_view->extraction = $this->extractSource($exception);

        // render the error page contents
        $this->_view->contents = $this->_view->render('diagnostics');

        // render the error layout
        $html = $this->_view->render('layout');
        return $html;
    }

    /**
     * Extract the source code around where $exception occurred.
     *
     * @param  Exception  $exception  PHP exception
     * @return array                  line number => source code
     */
    public function extractSource($exception)
    {
        return $this->_extractor->extractSourceFromException($exception);
    }

    /**
     * Build a more readable traceback from an $exception. 
     * 
     * @see formatFrame()
     *
     * @param  Exception        $exception  PHP exception
     * @return array<stdClass>              Array of frames
     */
    public function formatTrace($exception)
    {
        // PHP's Exception class declares getTrace() as final, but
        // some exceptions need to doctor the trace.
        if ($exception instanceof Mad_Support_Exception) {
            $trace = $exception->getDoctoredTrace();
        } else {
            $trace = $exception->getTrace();
        }
        
        // build an array of objects for the trace frames
        $out = array();
        foreach ($trace as $frame) {
            $out[] = $this->formatFrame($frame);
        }
        
        // PHP's trace doesn't include the line where the error 
        // occurred, so we prepend it to get a full trace.
        if (isset($out[0])) {
            $frame = clone $out[0];
            $frame->file = $exception->getFile();
            $frame->fileStripped = $this->stripPath( $exception->getFile() );
            $frame->line = $exception->getLine();
            $frame->url = $this->linkTo($frame->file, $frame->line);
            array_unshift($out, $frame);
        }
  
        return $out;
    }

    /**
     * Given a single frame from a PHP exception trace, return an
     * object for that frame with properties $file, $line, and $method.
     *
     * @param  array  $frame  PHP trace fram
     * @return object         Equivalent object
     */
    public function formatFrame($frame) 
    {
        $file = isset($frame['file'])  ? $frame['file'] : 'Unknown file';
        $line = isset($frame['line'])  ? $frame['line'] : '?';

        $method  = isset($frame['class'])    ? $frame['class']    : '';
        $method .= isset($frame['type'])     ? $frame['type']     : '';
        $method .= isset($frame['function']) ? $frame['function'] : '';
        
        return (object)array('file' => $file,
                             'fileStripped' => $this->stripPath($file),
                             'url'  => $this->linkTo($file, $line),
                             'line' => $line, 
                             'method' => $method);
    }

    /**
     * Given a $path, strip the MAD_ROOT and Mad stream wrapper protocol.
     *
     * @param  string  $path  Path to strip
     * @return string         Stripped path
     */     
    public function stripPath($path)
    {
        $mad_root = rtrim(MAD_ROOT, '/'.DIRECTORY_SEPARATOR) . '/';
        $path = str_replace($mad_root, '', $path);
        $path = $this->stripMadProtocols($path);
        return $path;
    }

    /**
     * Strip the Mad protocols like madview:// from the path
     * to make it more readable.
     *
     * @param  string  $path
     * @return string
     */
    public function stripMadProtocols($path) {
        return preg_replace('!^mad\w+://!', '', $path);
    }

    /**
     * Link to $line number in source file at $path.
     *
     * @param  string   $path  Path to source code
     * @param  integer  $line  Line number in source
     * @return string          URL
     */
    public function linkTo($path, $line)
    {
        $url = 'file://' . $this->stripMadProtocols($path);

        if (PHP_OS == 'Darwin') {
            $url = "txmt://open/?url=$url&line=$line";
        }

        return $url;
    }

}
