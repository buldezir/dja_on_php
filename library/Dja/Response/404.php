<?php

class Dja_Response_404 extends Dja_Response
{
    public function __construct(Dja_Route $route = null)
    {
        parent::__construct('<h1>404</h1>');
        $this->setHttpResponseCode(404);
        if (CONFIG()->DEBUG) {
            //echo dump(APP()->getRequest()->getPathInfo());
            $this->setBody(Dja_Template::getInstance()->render('404.html', array('urlpatterns' => $route->getResolveLog())));
        }
        //var_dump($GLOBALS);
    }
}