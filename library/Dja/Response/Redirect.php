<?php

class Dja_Response_Redirect extends Dja_Response
{
    public function __construct($url)
    {
        $this->setRedirect($url);
        $this->sendResponse();
    }
}