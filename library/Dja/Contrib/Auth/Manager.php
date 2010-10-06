<?php
/**
 * юзать примерно так :
 * function view_login(Dja_Request $request)
 * {
 *     $username = $request->getParam('username');
 *     $password = $request->getParam('password');
 *     $user     = Dja_Auth_Manager::authenticate($username, $password);
 *     if ($user !== false) {
 *         if ($user->is_active === true) {
 *             Dja_Auth_Manager::login($request, $user);
 *             return new Dja_Response_Redirect('/somewhere');
 *         } else {
 *             return new Dja_Response('account is disabled');
 *         }
 *     } else {
 *         return new Dja_Response('invalid username or password');
 *     }
 * }
 */
class Dja_Auth_Manager
{
    /**
     * Попытка получить объект пользователя
     * @param $username
     * @param $password
     * @return Dja_Contrib_Auth_Models__User|false
     */
    public static function authenticate($username, $password)
    {
        $authModel = CONFIG()->AUTH->model;
        $user = $authModel::objects()->get($username, 'username');
        if (!$user instanceof Dja_Model) {
            return false;
        }
        $passwHash = self::getPasswordHash($password);
        if ($user->password !== $passwHash) {
            return false;
        }
        return $user;
    }
    
    /**
     * Сохранение данных аутеттификации (в сессию)
     */
    public static function login(Dja_Requst $request, Dja_Model $user)
    {
        $request->session->auth_user_id = $user->getPrimaryKeyValue();
        if ($user instanceof Dja_Contrib_Auth_Models__User) {
            $user->last_login = date('Y-m-d H:i:s');
            $user->save();
        }
    }
    
    /**
     * some logout logic :)
     */
    public static function logout($request)
    {
        $request->session->auth_user_id = null;
    }
    
    public static function getUser(Dja_Requst $request)
    {
        // check if userId in auth session and try to get that user
        if (isset($request->session->auth_user_id)) {
            $authModel = CONFIG()->AUTH->model;
            $user = $authModel::objects()->get($request->session->auth_user_id);
            if (!$user instanceof Dja_Model) {
                $user = new Dja_Contrib_Auth_Models__AnonymousUser();
            }
        } else {
            $user = new Dja_Contrib_Auth_Models__AnonymousUser();
        }
        return $user;
    }
    
    public static function getPasswordHash($pwd)
    {
        return sha1($pwd);
    }
}