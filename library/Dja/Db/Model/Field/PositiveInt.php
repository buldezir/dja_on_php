<?php

class Dja_Db_Model_Field_PositiveInt extends Dja_Db_Model_Field_Base
{
    public function __construct(array $options = array())
    {
        $this->_options['max_length'] = 10;
        parent::__construct($options);
        
        $this->_options['type'] = 'PositiveIntegerField';
    }
    
    public function isValid($value)
    {
        if (preg_match('#^\d+$#', $value) && $value >= 0) {
            return true;
        }
        return false;
    }
}