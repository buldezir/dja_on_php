<?php

class Dja_Middleware_Test extends Dja_Middleware
{
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
        //$response->appendBody('<p><b>'.__METHOD__.'</b> path: '.$this->_request->getPathInfo().'</p>');
        //$response->setReadOnly(true);
        //return $response;
        return renderToResponse('index.html');
    }
}