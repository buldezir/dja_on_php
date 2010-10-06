<?php

class Dja_Contrib_Session_Models__Session extends Dja_Db_Model
{
    protected static $_fieldConfig = array(
        'id'       => array('Dja_Db_Model_Field_Char', 'primary_key' => true, 'max_length' => 32),
        'expire'   => array('Dja_Db_Model_Field_PositiveInt'),
        'data'     => array('Dja_Db_Model_Field_Text'),
    );
}