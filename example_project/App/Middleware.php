<?php
class App_Middleware extends Dja_Middleware
{
    public function processResponse($response)
    {
        $response->appendBody('<p><b>'.__METHOD__.'</b></p>');
        return $response;
    }
}