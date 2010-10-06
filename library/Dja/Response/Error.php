<?php

class Dja_Response_Error extends Dja_Response
{
    public function __construct(Exception $exception)
    {
        parent::__construct('<pre>'.$exception.'</pre>');
    }
}