<?php

class Dja_Db_Model_Field_Char extends Dja_Db_Model_Field_Base
{
    public function __construct(array $options = array())
    {
        $this->_options['max_length'] = 255;
        parent::__construct($options);
        
        $this->_options['type'] = 'CharField';
    }
    
    public function getSqlType()
    {
        return 'varchar';
    }
    
    public function isValid($value)
    {
        if (is_string($value) && strlen($value) <= $this->getOption('max_length')) {
            return true;
        }
        return false;
    }
}