<?php

class Dja_Db_Model_Field_Auto extends Dja_Db_Model_Field_Int
{
    public function __construct($options)
    {
        $this->_options['primary_key'] = true;
        $this->_options['auto_increment'] = true;
        $this->_options['editable'] = false;
        
        parent::__construct($options);
        
        $this->_options['type'] = 'AutoField';
    }
}