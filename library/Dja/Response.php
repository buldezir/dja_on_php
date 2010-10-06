<?php

class Dja_Response
{
    /**
     * Array of headers. Each header is an array with keys 'name' and 'value'
     * @var array
     */
    protected $_headers = array();
    
    protected $_httpResponseCode = 200;
    
    protected $_isRedirect = false;
    
    protected $_readOnly = false;
    
    protected $_responseBody = '';
    
    public function __construct($plainBody)
    {
        $this->setBody($plainBody);
    }
    
    public function setReadOnly($flag)
    {
        $this->_readOnly = (bool) $flag;
        return $this;
    }
    
    public function getBody()
    {
        return $this->_responseBody;
    }
    
    public function setBody($content)
    {
        if ($this->_readOnly) {
            trigger_error('Response is readonly!');
            return $this;
        }
        $this->_responseBody = $content;
        return $this;
    }
    
    public function appendBody($s)
    {
        if ($this->_readOnly) {
            trigger_error('Response is readonly!');
            return $this;
        }
        if (is_string($s)) {
            $this->_responseBody .= $s;
        } elseif ($s instanceof Dja_Response) {
            $this->_responseBody .= $s->getBody();
        }
        return $this;
    }
    
    public function prependBody($s)
    {
        if ($this->_readOnly) {
            trigger_error('Response is readonly!');
            return $this;
        }
        if (is_string($s)) {
            $this->_responseBody = $s . $this->_responseBody;
        } elseif ($s instanceof Dja_Response) {
            $this->_responseBody = $s->getBody() . $this->_responseBody;
        }
        return $this;
    }
    
    public function outputBody()
    {
        echo $this->_responseBody;
    }
    
    /**
     * Send the response, including all headers
     *
     * @return void
     */
    public function sendResponse()
    {
        $this->sendHeaders();
        $this->outputBody();
    }

    /**
     * Magic __toString functionality
     *
     * Proxies to {@link sendResponse()} and returns response value as string
     * using output buffering.
     *
     * @return string
     */
    public function __toString()
    {
        ob_start();
        $this->sendResponse();
        return ob_get_clean();
    }
    
    public function isRedirect()
    {
        return $this->_isRedirect;
    }
    
    public function getHttpResponseCode()
    {
        return $this->_httpResponseCode;
    }
    
    public function setHttpResponseCode($code)
    {
        if (!is_int($code) || (100 > $code) || (599 < $code)) {
            throw new Dja_Exception('Invalid HTTP response code');
        }
        if ((300 <= $code) && (307 >= $code)) {
            $this->_isRedirect = true;
        } else {
            $this->_isRedirect = false;
        }
        $this->_httpResponseCode = $code;
        return $this;
    }
    
    public function clearHeaders()
    {
        $this->_headers = array();
        return $this;
    }
    
    public function setHeader($name, $value, $replace = false)
    {
        $name  = $this->_normalizeHeader($name);
        $value = (string) $value;
        if ($replace) {
            foreach ($this->_headers as $key => $header) {
                if ($name == $header['name']) {
                    unset($this->_headers[$key]);
                }
            }
        }
        $this->_headers[] = array(
            'name'    => $name,
            'value'   => $value,
            'replace' => $replace
        );
        return $this;
    }
    
    public function sendHeaders()
    {
        // Only check if we can send headers if we have headers to send
        if ((count($this->_headers) == 0 && 200 == $this->_httpResponseCode) || headers_sent()) {
            return $this;
        }
        $httpCodeSent = false;
        foreach ($this->_headers as $header) {
            if (!$httpCodeSent && $this->_httpResponseCode) {
                header($header['name'] . ': ' . $header['value'], $header['replace'], $this->_httpResponseCode);
                $httpCodeSent = true;
            } else {
                header($header['name'] . ': ' . $header['value'], $header['replace']);
            }
        }
        if (!$httpCodeSent) {
            header('HTTP/1.1 ' . $this->_httpResponseCode);
            $httpCodeSent = true;
        }
        return $this;
    }
    
    public function setRedirect($url, $code = 302)
    {
        $this->setHeader('Location', $url, true)
             ->setHttpResponseCode($code);

        return $this;
    }
    
    /**
     * Normalize a header name
     *
     * Normalizes a header name to X-Capitalized-Names
     *
     * @param  string $name
     * @return string
     */
    protected function _normalizeHeader($name)
    {
        $filtered = str_replace(array('-', '_'), ' ', (string) $name);
        $filtered = ucwords(strtolower($filtered));
        $filtered = str_replace(' ', '-', $filtered);
        return $filtered;
    }
}