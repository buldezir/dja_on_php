<?php

class Dja_Db_Model_Field_Int extends Dja_Db_Model_Field_Base
{
    public function __construct(array $options = array())
    {
        $this->_options['max_length'] = 10;
        parent::__construct($options);
        
        $this->_options['type'] = 'IntegerField';
    }
    
    public function isValid($value)
    {
        if (preg_match('#^\d+$#', $value)) {
            return true;
        }
        return false;
    }
}