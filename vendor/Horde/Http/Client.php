<?php
/**
 * @package Horde_Http_Client
 */
class Horde_Http_Client {

    /**
     * URI to make our next request to
     *
     * This can be set directly, in the contructor, or overridden in
     * any of the request methods.
     *
     * @var string
     */
    public $uri = null;

    /**
     * @var array
     */
    protected $_headers = array();

    /**
     * The most recent HTTP request
     *
     * An array with these values:
     *   'uri'
     *   'method'
     *   'headers'
     *   'data'
     *
     * @var array
     */
    protected $_lastRequest;

    /**
     * The most recent HTTP response
     *
     * Horde_Http_Client_Response
     */
    protected $_lastResponse;

    /**
     * Horde_Http_Client constructor.
     *
     * @param string $uri Specify the URI to access.
     * @param array $headers Hash of header + value pairs to send with our request.
     */
    public function __construct($uri = null, $headers = array())
    {
        $this->uri = $uri;
        $this->setHeaders($headers);
    }

    /**
     * Set one or more headers
     *
     * @param mixed $headers A hash of header + value pairs, or a single header name
     * @param string $value  A header value
     */
    public function setHeaders($headers, $value = null)
    {
        if (!is_array($headers)) {
            $headers = array($headers => $value);
        }

        foreach ($headers as $header => $value) {
            $this->_headers[$header] = $value;
        }
    }

    /**
     * Get the current value of $header
     *
     * @param string $header Header name to get
     * @return string $header's current value
     */
    public function getHeader($header)
    {
        return isset($this->_headers[$header]) ? $this->_headers[$header] : null;
    }

    /**
     * Send a GET request
     *
     * @return Horde_Http_Client_Response
     */
    public function GET($uri = null, $headers = array())
    {
        return $this->sendRequest('GET', $uri, null, $headers);
    }

    /**
     * Send a POST request
     *
     * @return Horde_Http_Client_Response
     */
    public function POST($uri = null, $data = null, $headers = array())
    {
        return $this->sendRequest('POST', $uri, $data, $headers);
    }

    /**
     * Send a PUT request
     *
     * @return Horde_Http_Client_Response
     */
    public function PUT($uri = null, $data = null, $headers = array())
    {
        // FIXME: suport method override (X-Method-Override: PUT).
        return $this->sendRequest('PUT', $uri, $data, $headers);
    }

    /**
     * Send a DELETE request
     *
     * @return Horde_Http_Client_Response
     */
    public function DELETE($uri = null, $headers = array())
    {
        // FIXME: suport method override (X-Method-Override: DELETE).
        return $this->sendRequest('DELETE', $uri, null, $headers);
    }

    /**
     * Send an HTTP request
     *
     * @param string $method HTTP request method (GET, PUT, etc.)
     * @param string $uri URI to request, if different from $this->uri
     * @param mixed $data Request data. Can be an array of form data that will be
     *                    encoded automatically, or a raw string.
     * @param array $headers Any headers specific to this request. They will
     *                       be combined with $this->_headers, and override
     *                       headers of the same name for this request only.
     *
     * @return Horde_Http_Client_Response
     */
    public function sendRequest($method, $uri = null, $data = null, $headers = array())
    {
        if (is_null($uri)) {
            $uri = $this->uri;
        }

        if (is_array($data)) {
            $data = http_build_query($data, '', '&');
        }

        $headers = array_merge($this->_headers, $headers);

        // Store the last request for ease of debugging.
        $this->_lastRequest = array(
            'uri' => $uri,
            'method' => $method,
            'headers' => $headers,
            'data' => $data,
        );

        $opts = array('http' => array(
            'method' => $method,
            'header' => implode("\n", $headers),
            'content' => $data));
        $context = stream_context_create($opts);

        ini_set('display_errors', 0);
        ini_set('track_errors', 1);
        $stream = fopen($uri, 'rb', false, $context);
        ini_restore('track_errors');
        ini_restore('display_errors');

        if (!$stream) {
            throw new Horde_Http_Client_Exception('Problem with ' . $uri . ': ' . $php_errormsg);
        }

        $meta = stream_get_meta_data($stream);
        $headers = isset($meta['wrapper_data']) ? $meta['wrapper_data'] : array();

        $this->_lastResponse = new Horde_Http_Client_Response($uri, $stream, $headers);
        return $this->_lastResponse;
    }

    /**
     * Return the most recent request.
     */
    public function getLastRequest()
    {
        return $this->_lastRequest;
    }

    /**
     * Return the most recent Horde_Http_Client_Response
     *
     * @return Horde_Http_Client_Response
     */
    public function getLastResponse()
    {
        return $this->_lastResponse;
    }

}
