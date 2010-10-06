<?php
abstract class Dja_Middleware
{
    /**
     *
     * @var Dja_Request
     */
    protected $_request;
    
    final public function setRequest(Dja_Request $r)
    {
        $this->_request = $r;
        return $this;
    }
    
    public function processRequest()
    {
        
    }
    
    public function processView($callback, $callbackparams)
    {
        
    }
    
    public function processException(Exception $exception)
    {
        
    }
    
    public function processResponse(Dja_Response $response)
    {
        return $response;
    }
}