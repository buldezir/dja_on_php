<?php

class Dja_Request
{
    const SCHEME_HTTP  = 'http';
    const SCHEME_HTTPS = 'https';
    const URI_DELIMITER = '/';
    
    protected $_params = array();
    
    protected $_contexts = array();
    
    public function __get($key)
    {
        if (array_key_exists($key, $this->_contexts)) {
            return $this->_contexts[$key];
        }
        return null;
    }
    
    public function __set($key, $value)
    {
        if (!array_key_exists($key, $this->_contexts)) {
            $this->_contexts[$key] = $value;
        } else {
            throw new Dja_Exception('Cant overwrite request context!');
        }
    }
    
    public function __isset($key)
    {
        return array_key_exists($key, $this->_contexts);
    }
    
    public function getPathInfo()
    {
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        $path = str_replace($basePath, '', $_SERVER['REQUEST_URI']);
        // Remove the query string from REQUEST_URI
        if ($pos = strpos($path, '?')) {
            $path = substr($path, 0, $pos);
        }
        $path = trim($path, self::URI_DELIMITER);
        return $path;
    }
    
    public function getParam($key, $default = null)
    {
        if (isset($this->_params[$key])) {
            return $this->_params[$key];
        } elseif (isset($_POST[$key])) {
            return $_POST[$key];
        } elseif (isset($_GET[$key])) {
            return $_GET[$key];
        }
        return $default;
    }
    
    public function getRequestParams()
    {
        return $this->_params;
    }
    
    public function getParams()
    {
        $result = array();
        foreach ($_GET as $key => $val) {
            $result[$key] = $val;
        }
        foreach ($_POST as $key => $val) {
            $result[$key] = $val;
        }
        foreach ($this->_params as $key => $val) {
            $result[$key] = $val;
        }
        return $result;
    }
    
    public function getHeader($header)
    {
        // Try to get it from the $_SERVER array first
        $temp = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
        if (!empty($_SERVER[$temp])) {
            return $_SERVER[$temp];
        }
        // This seems to be the only way to get the Authorization header on Apache
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (!empty($headers[$header])) {
                return $headers[$header];
            }
        }
        return false;
    }
    
    /**
     * Retrieve a member of the $_COOKIE superglobal
     *
     * If no $key is passed, returns the entire $_COOKIE array.
     *
     * @todo How to retrieve from nested arrays
     * @param string $key
     * @param mixed $default Default value to use if key not found
     * @return mixed Returns null if key does not exist
     */
    public function getCookie($key = null, $default = null)
    {
        if (null === $key) {
            return $_COOKIE;
        }

        return (isset($_COOKIE[$key])) ? $_COOKIE[$key] : $default;
    }

    /**
     * Retrieve a member of the $_SERVER superglobal
     *
     * If no $key is passed, returns the entire $_SERVER array.
     *
     * @param string $key
     * @param mixed $default Default value to use if key not found
     * @return mixed Returns null if key does not exist
     */
    public function getServer($key = null, $default = null)
    {
        if (null === $key) {
            return $_SERVER;
        }

        return (isset($_SERVER[$key])) ? $_SERVER[$key] : $default;
    }
    
    /**
     * Return the method by which the request was made
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->getServer('REQUEST_METHOD');
    }
    
    /**
     * Get the request URI scheme
     *
     * @return string
     */
    public function getScheme()
    {
        return ($this->getServer('HTTPS') == 'on') ? self::SCHEME_HTTPS : self::SCHEME_HTTP;
    }

    /**
     * Get the HTTP host.
     *
     * "Host" ":" host [ ":" port ] ; Section 3.2.2
     * Note the HTTP Host header is not the same as the URI host.
     * It includes the port while the URI host doesn't.
     *
     * @return string
     */
    public function getHttpHost()
    {
        $host = $this->getServer('HTTP_HOST');
        if (!empty($host)) {
            return $host;
        }

        $scheme = $this->getScheme();
        $name   = $this->getServer('SERVER_NAME');
        $port   = $this->getServer('SERVER_PORT');

        if (($scheme == self::SCHEME_HTTP && $port == 80) || ($scheme == self::SCHEME_HTTPS && $port == 443)) {
            return $name;
        } else {
            return $name . ':' . $port;
        }
    }

    /**
     * Get the client's IP addres
     *
     * @param  boolean $checkProxy
     * @return string
     */
    public function getClientIp($checkProxy = true)
    {
        if ($checkProxy && $this->getServer('HTTP_CLIENT_IP') != null) {
            $ip = $this->getServer('HTTP_CLIENT_IP');
        } else if ($checkProxy && $this->getServer('HTTP_X_FORWARDED_FOR') != null) {
            $ip = $this->getServer('HTTP_X_FORWARDED_FOR');
        } else {
            $ip = $this->getServer('REMOTE_ADDR');
        }

        return $ip;
    }
    
    public function getAbsoluteUrl()
    {
        return $this->getScheme().'://'.$this->getHttpHost().$this->getServer('REQUEST_URI');
    }

    /**
     * Was the request made by POST?
     *
     * @return boolean
     */
    public function isPost()
    {
        if ('POST' == $this->getMethod()) {
            return true;
        }

        return false;
    }
    
    public function isAjax()
    {
        return $this->isXmlHttpRequest();
    }
    
    /**
     * Is the request a Javascript XMLHttpRequest?
     *
     * Should work with Prototype/Script.aculo.us, possibly others.
     *
     * @return boolean
     */
    public function isXmlHttpRequest()
    {
        return ($this->getHeader('X_REQUESTED_WITH') == 'XMLHttpRequest');
    }

    /**
     * Is this a Flash request?
     *
     * @return bool
     */
    public function isFlashRequest()
    {
        $header = strtolower($this->getHeader('USER_AGENT'));
        return (strstr($header, ' flash')) ? true : false;
    }
}