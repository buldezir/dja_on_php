<?php

class Dja_Contrib_Session_Handler_Model implements Dja_Contrib_Session_Handler_Interface
{
    protected $_lifetime;
    
    protected $_modified = false;
    
    public function __construct()
    {
        $this->_lifetime = (int) ini_get('session.gc_maxlifetime');
    }
    
    public function __destruct()
    {
        session_write_close();
    }
    
    public function __get($key)
    {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }
    
    public function __isset($key)
    {
        return isset($_SESSION[$key]);
    }
    
    public function __set($key, $val)
    {
        if ($val === null) {
            unset($_SESSION[$key]);
        } else {
            $_SESSION[$key] = $val;
        }
        $this->_modified = true;
    }
    
    public function open($save_path, $name)
    {
        return true;
    }
    
    public function close()
    {
        return true;
    }
    
    public function read($id)
    {
        $return = '';
        $row = Dja_Contrib_Session_Models__Session::objects()->get($id);
        if ($row) {
            if ($row->expire > time()) {
                $return = $row->data;
            } else {
                $row->delete();
            }
        }
        return $return;
    }
    
    public function write($id, $data)
    {
        if ($this->_modified === false) {
            return true;
        }
        $expire = $this->_lifetime + time();
        $row = Dja_Contrib_Session_Models__Session::objects()->get($id);
        if (!$row) {
            $row = new Dja_Contrib_Session_Models__Session();
            $row->id = $id;
        }
        $row->data = $data;
        $row->expire = $expire;
        return $row->save();
    }
    
    public function destroy($id)
    {
        $row = Dja_Contrib_Session_Models__Session::objects()->get($id);
        if ($row) {
            return $row->delete();
        } else {
            return false;
        }
    }
    
    public function gc($maxlifetime)
    {
        Dja_Contrib_Session_Models__Session::objects()->filter(new Dja_Db_Expr('expire < UNIX_TIMESTAMP()'))->delete();
        return true;
    }
}