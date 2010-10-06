<?php

class Dja_Response_Ajax extends Dja_Response
{
    public function __construct($plainBody)
    {
        parent::__construct($plainBody);
        $this->setHeader('Content-Type', 'application/json', true);
    }
    
    public function setBody($content)
    {
        $this->_responseBody = json_encode($content);
    }
}