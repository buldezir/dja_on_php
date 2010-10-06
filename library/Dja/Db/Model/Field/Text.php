<?php

class Dja_Db_Model_Field_Text extends Dja_Db_Model_Field_Base
{
    public function __construct(array $options = array())
    {
        parent::__construct($options);
        
        $this->_options['type'] = 'TextField';
    }
    
    public function isValid($value)
    {
        return true;
    }
}