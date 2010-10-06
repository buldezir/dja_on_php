<?php
interface Dja_Db_Model_Field_SingleRelationInterface
{
    public function getRelObject($value);
    
    //protected function _setupBackwardsRelation();
}