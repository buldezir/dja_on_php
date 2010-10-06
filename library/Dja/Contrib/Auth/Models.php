<?php

class Dja_Contrib_Auth_Models__Group extends Dja_Db_Model
{
    protected static $_fieldConfig = array(
        'id'   => array('Dja_Db_Model_Field_Auto'),
        'name' => array('Dja_Db_Model_Field_Char'),
    );
}

class Dja_Contrib_Auth_Models__User extends Dja_Db_Model
{
    protected static $_fieldConfig = array(
        'id'           => array('Dja_Db_Model_Field_Auto'),
        'username'     => array('Dja_Db_Model_Field_Char', 'max_length' => 30),
        'first_name'   => array('Dja_Db_Model_Field_Char', 'max_length' => 30, 'is_null' => true),
        'last_name'    => array('Dja_Db_Model_Field_Char', 'max_length' => 30, 'is_null' => true),
        'email'        => array('Dja_Db_Model_Field_Char'),
        'password'     => array('Dja_Db_Model_Field_Char', 'max_length' => 40),
        'is_staff'     => array('Dja_Db_Model_Field_Bool'),
        'is_active'    => array('Dja_Db_Model_Field_Bool'),
        'is_superuser' => array('Dja_Db_Model_Field_Bool'),
        'last_login'   => array('Dja_Db_Model_Field_DateTime'),
        'date_joined'  => array('Dja_Db_Model_Field_DateTime'),
        'groups'       => array('Dja_Db_Model_Field_ManyToMany', 'relationClass' => 'Dja_Contrib_Auth_Models__Group', 'related_name' => 'users'),
    );
    
    public function __toString()
    {
        if (!empty($this->username)) {
            return $this->username;
        }
        return 'noname_'.$this->id;
    }
    
    public function getFullName()
    {
        $s = array();
        if (!empty($this->first_name)) {
            $s[] = $this->first_name;
        }
        if (!empty($this->last_name)) {
            $s[] = $this->last_name;
        }
        return implode(' ', $s);
    }
    
    public function isAnonymous()
    {
        return false;
    }
    
    public function isAuthenticated()
    {
        return true;
    }
}

class Dja_Contrib_Auth_Models__AnonymousUser
{
    public $id = null;
    
    public function __toString()
    {
        return 'Anonymous';
    }
    
    public function isAnonymous()
    {
        return true;
    }
    
    public function isAuthenticated()
    {
        return false;
    }
    
    public function save()
    {
        throw new Dja_Exception('Cannot save anonymous user object');
    }
    
    public function __get($key)
    {
        return null;
    }
    
    public function __set($key, $val)
    {
        return false;
    }

    public function __isset($key)
    {
        return false;
    }
}