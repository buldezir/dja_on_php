<?php

class Dja_Contrib_Auth_Middleware extends Dja_Middleware
{
    public function processRequest()
    {
        if (!isset($this->_request->session)) {
            throw new Dja_Exception("The Dja authentication middleware requires session middleware to be installed. Edit your MIDDLEWARE setting to insert 'Dja_Contrib_Session_Middleware'.");
        }
        $this->_request->user = Dja_Auth_Manager::getUser($this->_request);
    }
}