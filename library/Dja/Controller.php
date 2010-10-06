<?php

abstract class Dja_Controller
{
    /**
     *
     * @param $template
     * @param $data
     * @return Dja_Response
     */
    public function renderToResponse($template, $data = array())
    {
        return renderToResponse($template, $data);
    }
    
    protected function _redirect($url)
    {
        
    }
}