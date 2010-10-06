<?php

class Dja_Contrib_Session_Middleware extends Dja_Middleware
{
    protected $_saveHandler;
    public function __construct()
    {
        $defaultCookieParams = session_get_cookie_params();
        $cookieParams = CONFIG()->SESSION->cookie_params->toArray();
        foreach ($defaultCookieParams as $key => $val) {
            if (!isset($cookieParams[$key])) {
                $cookieParams[$key] = $val;
            }
        }
        session_set_cookie_params(
            $cookieParams['lifetime'],
            $cookieParams['path'],
            $cookieParams['domain'],
            $cookieParams['secure'],
            $cookieParams['httponly']
        );
        ini_set('session.gc_maxlifetime', ($cookieParams['lifetime'] > 0 ? $cookieParams['lifetime'] : 1440));
        
        session_name(CONFIG()->SESSION->cookie_name);
        $handler = CONFIG()->SESSION->handler;
        $this->_saveHandler = new $handler();
        if ($this->_saveHandler instanceof Dja_Contrib_Session_Handler_Interface) {
            session_set_save_handler(
                array(&$this->_saveHandler, 'open'),
                array(&$this->_saveHandler, 'close'),
                array(&$this->_saveHandler, 'read'),
                array(&$this->_saveHandler, 'write'),
                array(&$this->_saveHandler, 'destroy'),
                array(&$this->_saveHandler, 'gc')
            );
        } else {
            throw new Dja_Exception('CONFIG()->SESSION->handler must implement Dja_Contrib_Session_Handler_Interface');
        }
    }
    
    public function processRequest()
    {
        session_start();
        $this->_request->session = $this->_saveHandler;
    }
}