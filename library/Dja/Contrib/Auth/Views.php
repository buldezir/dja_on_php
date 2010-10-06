<?php

class Dja_Auth_Views
{
    public function login(Dja_Request $request)
    {
        $username = $request->getParam('username');
        $password = $request->getParam('password');
        $user     = Dja_Auth_Manager::authenticate($username, $password);
        if ($user !== false) {
            if ($user->is_active === true) {
                Dja_Auth_Manager::login($request, $user);
                return new Dja_Response_Redirect($request->getParam('redirect_to', '/'));
            } else {
                return new Dja_Response('account is disabled');
            }
        } else {
            return new Dja_Response('invalid username or password');
        }
    }
    
    public function logout(Dja_Request $request)
    {
        Dja_Auth_Manager::logout($request);
        return new Dja_Response_Redirect($request->getParam('redirect_to', '/'));
    }
}