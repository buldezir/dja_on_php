<?php

class Dja_Request_Context_Auth extends Dja_Request_Context
{
    public function getName()
    {
        return 'user';
    }
    
    public function init(Dja_Request $request)
    {
        
    }
    
    public function getValue()
    {
        return 'User_Obj';
    }
}