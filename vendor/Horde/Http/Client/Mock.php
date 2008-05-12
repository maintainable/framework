<?php
/**
 * @package Horde_Http_Client
 */
class Horde_Http_Client_Mock extends Horde_Http_Client {

    protected $_responses = array();

    protected $_responseIndex = 0;

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
     *
     * @TODO make getLastRequest work somehow.
     */
    public function sendRequest($method, $uri = null, $data = null, $headers = array())
    {
        if ($this->_responseIndex >= count($this->_responses)) {
            $this->_responseIndex = 0;
        }
        return $this->_responses[$this->_responseIndex++];
    }

    /**
     * Set the HTTP response(s) to be returned by this adapter
     *
     * @param Horde_Http_Client_Response $response
     */
    public function setResponse($response)
    {
    	$this->_responses = array($response);
    	$this->_responseIndex = 0;
    }

    /**
     * Add another response to the response buffer.
     *
     * @param string $response
     */
    public function addResponse($response)
    {
        $this->_responses[] = $response;
    }

    /**
     * Sets the position of the response buffer.  Selects which
     * response will be returned on the next call to read().
     *
     * @param integer $index
     */
    public function setResponseIndex($index)
    {
        if ($index < 0 || $index >= count($this->_responses)) {
            throw new OutOfBoundsException();
        }
        $this->_responseIndex = $index;
    }

}
