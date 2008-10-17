<?php
/**
 * @category   Mad
 * @package    Mad_Mailer
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * @category   Mad
 * @package    Mad_Mailer
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Mailer_Base
{
    /*##########################################################################
    # Configuration options
    ##########################################################################*/

    /**
     * How should the mail be delivered
     *  - sendmail: PHP's default mechanism for sending messages
     *  - test:     Don't actually deliver the message
     */
    public static $deliveryMethod = 'sendmail';

    /*##########################################################################
    # Private Attributes
    ##########################################################################*/
    
    /**
     * The list of attachment names
     */
    private $_attachments = array();

    /**
     * Comma separated recipient string
     * @var string
     */
    private $_recipients = null; 

    /**
     * CRLF Delimited string of message headers
     * @var string
     */
    private $_headers = null;

    /**
     * CRLF Delimited string of message body
     * @var string
     */
    private $_body = null; 


    /**
     * Logger object
     * @var Zend_Log
     */
    private $_logger = null;


    /*##########################################################################
    # Message Attributes
    ##########################################################################*/

    /**
     * An associative array used to pass values to the template that contains 
     * the e-mail
     * @var array
     */
    protected $body = array();

    /**
     * Blind-copy recipients, using the same format as $recipients. 
     * @var array|string
     */
    protected $bcc = array();

    /**
     * Carbon-copy recipients, using the same format as $recipients. 
     * @var array|string
     */
    protected $cc = array();

    /**
     * The character set used in the e-mail’s Content-Type header. 
     * Defaults to "utf-8". 
     */
    protected $charset = 'utf-8';

    /**
     * One or more e-mail addresses to appear on the From: line, using the same 
     * format as $recipients. 
     * @var array|string
     */
    protected $from = null;

    /**
     * A hash of header name/value pairs, used to add arbitrary header lines 
     * to the e-mail.
     * $this->headers["Organization"] = "Maintainable Software, LLC" 
     * @var array
     */
    protected $headers = array();

    /**
     * One or more recipient e-mail addresses. These may be simple addresses, 
     * such as derek@maintainable.com, or some identifying phrase followed by 
     * the e-mail address in angle brackets:
     * 
     * <code>
     * $this->recipients = array(
     *   "derek@maintainable.com", 
     *   "Mike Naberezny <mike@maintainable.com>"
     * );
     * @var array|string
     */
    protected $recipients = array();

    /**
     * A Timestamp that sets the e-mail’s Date: header. If not specified, 
     * the current date and time will be used. 
     * @var int
     */
    protected $sentOn = 0;

    /**
     * The subject line for the e-mail. 
     * @var string
     */
    protected $subject = null;

    /*##########################################################################
    # Construction
    ##########################################################################*/

    /**
     * Construct new mailer 
     */
    public function __construct()
    {
        $this->_logger = $GLOBALS['MAD_DEFAULT_LOGGER'];
    }

    /**
     * @param   string  $name
     * @param   array   $args
     */
    public function __call($name, $args)
    {
        // create message
        if (substr($name, 0, 6) == 'create') {
            $methodName = str_replace('create', '', $name);
            $methodName[0] = strtolower($methodName[0]);
            return $this->_create($methodName, $args);

        // deliver message
        } elseif (substr($name, 0, 7) == 'deliver') {
            $methodName = str_replace('deliver', '', $name);
            $methodName[0] = strtolower($methodName[0]);
            return $this->_deliver($methodName, $args);

        } else {
            throw new Mad_Mailer_Exception("Unrecognized method '$name'");
        }
    }


    /*##########################################################################
    # Public methods
    ##########################################################################*/

    /**
     * @return  string
     */
    public function getHeaders()
    {
        return $this->_headers;
    }

    /**
     * @return  string
     */
    public function getBody()
    {
        return $this->_body;
    }

    /**
     * @return  string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return  string
     */
    public function getRecipients()
    {
        return $this->_recipients;
    }

    /**
     * @return  array
     */
    public function getAttachments()
    {
        return $this->_attachments;
    }

    /**
     * @param   array   $options
     */
    public function attachment($options = array())
    {
        $valid = array('filename'         => 'attachment', 
                       'body'             => '',
                       'contentType'      => 'application/octet-stream',
                       'transferEncoding' => 'base64');        
        $options = Mad_Support_Base::assertValidKeys($options, $valid);
        
        // verify body & filename
        if (empty($options['body'])) {
            $msg = "Content for attachment ".$options['filename']." is empty";
            throw new Mad_Mailer_Exception($msg);
        }
        if (empty($options['filename'])) {
            $msg = "Filename for attachment ".$options['filename']." is empty";
            throw new Mad_Mailer_Exception($msg);            
        }

        // generate unique filename
        $filename = $options['filename'];
        if (!empty($this->_attachments[$filename])) {
            $i    = 0;
            $base = substr($filename, 0, strrpos($filename, '.'));
            $ext  = substr($filename, strrpos($filename, '.'));

            // increment to my_image_1, my_image_2, etc
            do {
                $i++;
                $filename = $base.'-'.$i.$ext;
            } while (!empty($this->_attachments[$filename]));

            $options['filename'] = $filename;
        }

        // add to list of attachments
        $this->_attachments[$filename] = $options;
    }

    /*##########################################################################
    # Create/Delivery
    ##########################################################################*/

    /**
     * Create an email message
     * 
     * @param   string  $methodName
     * @param   array   $args
     */
    protected function _create($methodName, $args)
    {
        return $this->_parseMessage($methodName, $args);
    }

    /**
     * Deliver an email message
     * 
     * @param   string  $methodName
     * @param   array   $args
     */
    protected function _deliver($methodName, $args)
    {
        $this->_parseMessage($methodName, $args);

        if (self::$deliveryMethod == 'sendmail') {
            return @mail($this->_recipients, $this->subject, 
                         $this->_body,       $this->_headers);
        } else {
            return true;
        }
    }


    /*##########################################################################
    # Build headers/body from attributes/templates
    ##########################################################################*/

    /**
     * Call the specific mailer method to set the instance variables
     * 
     * @param   string  $methodName
     * @param   array   $args
     */
    protected function _parseMessage($methodName, $args)
    {
        // defaults
        $this->_reset();

        // method sets instance variables
        call_user_func_array(array($this, $methodName), $args);
        
        // common mime boundary
        $this->_mimeBoundary = '__' . md5(time());

        $this->_parseMessageHeaders();
        $this->_parseMessageBody($methodName);
        $this->_logMessage();

        return $this->_getFullMessage();
    }

    /**
     * Before sending any messages, we need to reset the attributes
     */
    protected function _reset()
    {
        $this->body       = array();
        $this->bcc        = array();
        $this->cc         = array();
        $this->charset    = 'utf-8';
        $this->from       = null;
        $this->headers    = array();
        $this->recipients = array();
        $this->sentOn     = time();
        $this->subject    = null;

        $this->_headers    = null;
        $this->_body       = null;
        $this->_recipients = null;
    }

    /**
     * Parse options to build message headers string
     * 
     * @return  string
     */
    protected function _parseMessageHeaders()
    {
        // Recipients
        $this->_recipients = join(', ', (array)$this->recipients);

        // Common Headers
        $sentOn = is_int($this->sentOn) ? $this->sentOn : strtotime($this->sentOn);
        $headers = "Return-Path: $this->from\r\n";
        $headers .= "From: $this->from\r\n";
        $headers .= "Date: ".date('r', $sentOn)."\r\n";
        if ($this->cc) {
            $headers .= "Cc: ".join(', ', (array)$this->cc)."\r\n";
        }
        if ($this->bcc) {
            $headers .= "Bcc: ".join(', ', (array)$this->bcc)."\r\n";
        }
        // additional headers
        foreach ($this->headers as $key => $value) {
            $headers .= "$key: $value\r\n";
        }
        // Multipart message
        $headers .= "Mime-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/mixed; boundary=\"".$this->_mimeBoundary."\"\r\n";
        
        return $this->_headers = $headers;
    }

    /**
     * Parse options to build message body string
     * 
     * @param   string
     * @return  string
     */
    protected function _parseMessageBody($methodName)
    {
        // template for body - copy instance variables
        $view = new Mad_View_Base;
        foreach ($this->body as $key => $value) {
            $view->$key = $value;
        }
        $template = get_class($this)."/{$methodName}.html";
        $content  = $view->render($template);

        $body = "This is a message in MIME format.  If you see this,\r\n"
              . "your mail reader does not support the MIME format.\r\n\r\n";

        // Text body 
        $body .= "--{$this->_mimeBoundary}\r\n";
        $body .= "Content-Type: text/plain; charset=\"$this->charset\"\r\n";
        $body .= "Content-Disposition: inline\r\n";
        $body .= "Content-Transfer-Encoding: 8bit\r\n";
        $body .= "$content\r\n\r\n";

        // Attachments (important to keep the double newlines)
        foreach ($this->_attachments as $filename => $options) {
            $encoded = base64_encode($options['body']);
            $chunked = chunk_split($encoded, 76, "\r\n");
            
            $body .= "--{$this->_mimeBoundary}\r\n";
            $body .= "Content-Type: ".$options["contentType"]."; charset=\"$this->charset\"\r\n";
            $body .= "Content-Disposition: attachment; filename=\"$filename\"\r\n";
            $body .= "Content-Transfer-Encoding: ".$options['transferEncoding']."\r\n\r\n";
            $body .= $chunked."\r\n";
        }
        $body .= "--$this->_mimeBoundary--\r\n\r\n";

        return $this->_body = $body;
    }

    /**
     * Get the full message
     * @return string
     */
    protected function _getFullMessage()
    {
        return $this->_headers.$this->_body;
    }

    /**
     * Log the message
     */
    protected function _logMessage()
    {
        $this->_logger->info("MAIL \n".$this->_getFullMessage());
    }
}