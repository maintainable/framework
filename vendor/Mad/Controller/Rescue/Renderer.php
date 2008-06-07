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

        $this->_view->exception  = $exception;
        $this->_view->request    = $request;
        $this->_view->response   = $response;
        $this->_view->extraction = $this->extractSource($exception);

        // render the error page contents
        $this->_view->contents = $this->_view->render('diagnostics');

        // render the error layout
        $html = $this->_view->render('layout');
        return $html;
    }

    public function extractSource($exception)
    {
        return $this->_extractor->extractSourceFromException($exception);
    }

}
