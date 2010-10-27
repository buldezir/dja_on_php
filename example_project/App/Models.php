<?php

/*class App_Models__User extends Dja_Db_Model
{
    protected static $_fieldConfig = array(
        'id'           => array('Dja_Db_Model_Field_Auto'),
        'username'     => array('Dja_Db_Model_Field_Char', 'max_length' => 30),
        'first_name'   => array('Dja_Db_Model_Field_Char', 'max_length' => 30, 'is_null' => true),
        'last_name'    => array('Dja_Db_Model_Field_Char', 'max_length' => 30, 'is_null' => true),
        'email'        => array('Dja_Db_Model_Field_Char'),
        'password'     => array('Dja_Db_Model_Field_Char'),
        'is_staff'     => array('Dja_Db_Model_Field_Bool'),
        'is_active'    => array('Dja_Db_Model_Field_Bool'),
        'is_superuser' => array('Dja_Db_Model_Field_Bool'),
        'last_login'   => array('Dja_Db_Model_Field_DateTime'),
        'date_joined'  => array('Dja_Db_Model_Field_DateTime'),
        'groups'       => array('Dja_Db_Model_Field_ManyToMany', 'relationClass' => 'App_Models__Group', 'related_name' => 'users'),
    );
}

class App_Models__Group extends Dja_Db_Model
{
    protected static $_fieldConfig = array(
        'id'   => array('Dja_Db_Model_Field_Auto'),
        'name' => array('Dja_Db_Model_Field_Char'),
    );
}*/

class App_Models__Message extends Dja_Db_Model
{
    protected static $_fieldConfig = array(
        'sender'   => array('Dja_Db_Model_Field_ForeignKey', 'relationClass' => 'Dja_Contrib_Auth_Models__User', 'related_name' => 'msg_out'),
        'receiver' => array('Dja_Db_Model_Field_ForeignKey', 'relationClass' => 'Dja_Contrib_Auth_Models__User', 'related_name' => 'msg_in'),
        'title'    => array('Dja_Db_Model_Field_Char'),
    );
}