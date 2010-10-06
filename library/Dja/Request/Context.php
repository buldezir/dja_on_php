<?php

abstract class Dja_Request_Context
{
    abstract public function getName();
    
    abstract public function init(Dja_Request $request);

    public function getValue()
    {
        return $this;
    }
}