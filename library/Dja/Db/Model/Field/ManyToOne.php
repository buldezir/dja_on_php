<?php

class Dja_Db_Model_Field_ManyToOne extends Dja_Db_Model_Field_Base implements Dja_Db_Model_Field_ManyRelationInterface
{
    public function __construct($options)
    {
        $this->_options['selfColumn'] = null;
        $this->_options['refColumn']  = null;
        
        parent::__construct($options);
        $this->setOption('db_column', false);
        
        if (!$this->refColumn) {
            throw new Dja_Db_Exception('"using" is required option for Dja_Db_Model_Field_ManyToOne');
        }
    }
    
    public function getSqlType()
    {
        return false;
    }
    
    public function isRelation()
    {
        return true;
    }
    
    public function getRelQuery(Dja_Db_Model $model)
    {
        $relationClass = $this->relationClass;
        $throughSelf = $this->selfColumn!==null?$this->selfColumn:$relationClass::metadata()->getPrimaryKey()->name;
        $throughRef = $this->refColumn;
        return $relationClass::objects()->filter($throughRef, $model->{$throughSelf});
    }
    
    public function isValid($value)
    {
        if ($value instanceof Dja_Db_Model_Query) {
            return true;
        }
        return false;
    }
}