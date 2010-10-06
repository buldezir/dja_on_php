<?php

class Dja_Db_Model_Field_Bool extends Dja_Db_Model_Field_Base
{
    public function __construct(array $options = array())
    {
        $this->_options['max_length'] = 1;
        parent::__construct($options);
        
        $this->_options['type'] = 'BooleanField';
    }
    
    public function isValid($value)
    {
        return is_bool($value);
    }
    
    /**
     * converts data stored on field to php object/structure
     * @param $value
     * @return any type
     */
    public function dbToPhp($value)
    {
        return (bool)$value;
    }
    
    /**
     * converts php object/structure for put in db
     * @param $value
     * @return string|int
     */
    public function phpToDb($value)
    {
        $value = ($value===true?1:0);
        return $value;
    }
    
}