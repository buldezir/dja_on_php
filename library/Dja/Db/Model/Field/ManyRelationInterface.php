<?php
interface Dja_Db_Model_Field_ManyRelationInterface
{
    public function getRelQuery(Dja_Db_Model $model);
}