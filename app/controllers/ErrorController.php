<?php

class ErrorController extends ApplicationController
{
    /**
     * Email exception errors to
     */
    protected $_emailTo = array('email@example.com');

    /**
     * Initialize variables used through all methods
     */
    protected function _initialize()
    {
        // new style templates
        $this->useView(true);
        $this->useViewLayout(false);

        $this->setLayout('error');
        $this->setVar('PAGE_ID', 'error');
        $this->_displayMenu();
    }

    /**
     * Main error page.
     *  - On development we display error info to the developer.
     *  - On production we log the error and display a public message
     *    Some special errors will simply redirect to another
     *    page (as in the case of a 404 error)
     */
    public function index()
    {
        $publicMsg = 'An error occurred that prevents the application from running '.
                     'correctly. ' . COMPANY_NAME . ' has been notified of the problem and will be '.
                     'looking into it as soon as possible. To try and alleviate complications '.
                     'please restart your browser before continuing. Contact us '.
                     'directly if this problem persists.';

        // throw text error message if we don't have exception data
        $debugException = $this->params('exception');
        if (!isset($debugException)) {
            Logger::perror('Error occurred, but failed to save exception information');
            $this->renderText($publicMsg);
            return;
        }

        // get saved error from previous request
        $error = unserialize(base64_decode(urldecode($debugException)));

        // development env
        if (CURRENT_ENV == 'development') {
            $this->_developmentException($error);

        // test env
        } elseif (CURRENT_ENV == 'test') {
            // 404 Error
            if ($error->type == 'PageNotFoundException') {
                $this->_productionPageNotFound($error);
                $this->redirectTo('/error/page_not_found');
            } else {
                $this->_developmentException($error);
            }

        // production env
        } else {
            // 404 Error
            if ($error->type == 'PageNotFoundException') {
                $this->_productionPageNotFound($error);
                $this->redirectTo('/error/page_not_found');

            // log the error, and redirect to public error page
            } else {
                $this->_productionException($error);
                $this->renderText($publicMsg);
            }
        }
    }

    /**
     * Public 404 will redirect to display 'page not found'
     * display the default template
     */
    public function pageNotFound()
    {
        $this->setVar('PAGE_ID', 'pageNotFound');
        $this->setVar('TITLE',   'Page Not Found');
        $this->setLayout('application');
    }

    /**
     * An exception occurred in the development environment. Parse out error message
     * @param   object  $error
     * @param   string  $trace
     * @param   string  $request
     */
    private function _developmentException($error)
    {
        $error->backtrace = Logger::getTrace($error->trace, "\n");
        list($error->request, $error->response) = $this->_parseHttp($error->trace, "\n");
        $error->dump = print_r($error, true);

        $this->_view->error = $error;
    }

    /**
     * Chances are that a '404' are indications of a larger problem
     * @param   object  $error
     */
    private function _productionPageNotFound($error)
    {
        $error->backtrace = Logger::getTrace($error->trace, "\n");
        list($error->request, $error->response) = $this->_parseHttp($error->trace, "\n");

        // log
        Logger::perror('404 - Page Not Found Exception: '.
                       "\n\t type:    ".$error->type.
                       "\n\t file:    ".$error->file.
                       "\n\t line:    ".$error->line.
                       "\n\t user:    ".$error->user.
                       "\n\t message: ".$error->message.
                       "\n\t trace:   ".$error->trace);
    }

    /**
     * An exception occurred in the production environment. Parse out error message
     * @param   object  $error
     */
    private function _productionException($error)
    {
        $error->backtrace = Logger::getTrace($error->trace, "\n    ")
        list($error->request, $error->response) = $this->_parseHttp($error->trace, "\n    ");

        // log
        Logger::perror('Uncaught Exception: '.
                       "\n\t type:    ".$error->type .
                       "\n\t file:    ".$error->file .
                       "\n\t line:    ".$error->line .
                       "\n\t user:    ".$error->user .
                       "\n\t message: ".$error->message .
                       "\n\t trace:   \n    ".$error->backtrace);
        // email
        $from = "errors@example.com";
        $subject = "Uncaught Exception ";
        $headers = "From: Exception Error <errors@example.com>\r\n\r\n";
        $message = (
            "You are getting this message because a user got an uncaught exception ".
            "error and we send out this e-mail with the information.\n\n" .
            "type:     [$error->type]\n" .
            "file:     [$error->file]\n" .
            "line:     [$error->line]\n" .
            "user:     [$error->user]\n" .
            "message:  [$error->message]\n\n" .
            "trace:    \n    $error->backtrace\n\n" .
            "request:  \n    $error->request\n\n" .
            "response: \n    $error->response\n\n"
        );

        foreach ($this->_emailTo as $to) {
            mail($to, $subject, $message, $headers, $from);
        }
    }

    /**
     * Get Http info form the stack
     * @param   array   $stack  The stack trace
     * @param   string  $nl     newline char
     * @return  array
     */
    private function _parseHttp($stack, $nl="\n")
    {
        $sz = sizeof($stack);
        for ($i = 0; $i < $sz; $i++) {
            $bt = $stack[$i];
            if (empty($bt['args'])) continue;

            foreach ($bt['args'] as $arg) {
                // request/response objects get saved to display
                if ($arg instanceof HttpRequest) {
                    $request = $arg;
                } elseif ($arg instanceof HttpResponse) {
                    $response = $arg;
                }
            }
        }

        $requestStr = null;
        if (isset($request)) {
            $requestStr .= "url: $nl   ".$request->getPathUri().$nl;

            $i = 0;
            $cookies = array();
            foreach ($request->getCookie() as $key=>$val) {
                $i++;
                $cookies[] = "$i. $key => $val";
            }
            $i = 0;
            $sessions = array();
            foreach ($request->getSession() as $key=>$val) {
                $i++;
                $sessions[] = "$i. $key => $val";
            }
            $i = 0;
            $flashs = array();
            foreach ($request->getFlash() as $key=>$val) {
                $i++;
                $flashs[] = "$i. $key => $val";
            }
            $requestStr .= "cookies: $nl   ".implode("$nl   ", $cookies).$nl;
            $requestStr .= "session: $nl   ".implode("$nl   ", $sessions).$nl;
            $requestStr .= "flash: $nl   ".implode("$nl   ",   $flashs).$nl;
        }

        $responseStr = null;
        if (isset($response)) {
            $i = 0;
            foreach ($response->getHeaders() as $key=>$val) {
                $i++;
                $headers[] = "$i. $key";
            }
            $responseStr .= "headers: $nl   ".implode(", $nl   ", $headers).$nl;
            $responseStr .= "status: ".$response->getStatus().$nl;
            $responseStr .= "redirect: ".$response->getRedirectUrl().$nl;
        }
        return array($requestStr, $responseStr);
    }
}

?>