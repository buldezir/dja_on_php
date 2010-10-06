<?php

class Dja_Middleware_Common extends Dja_Middleware
{
    public function processRequest()
    {
        
    }
    
    public function processView($callback, $callbackparams)
    {
        
    }
    
    public function processException(Exception $exception)
    {
        return new Dja_Response_Error($exception);
    }
    
    public function processResponse(Dja_Response $response)
    {
        //$response->appendBody('<p><b>'.__METHOD__.'</b> path: '.$this->_request->getPathInfo().'</p>');
        //$response->setReadOnly(true);
        return $response;
        //return renderToResponse('index.html');
    }
}