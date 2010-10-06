<?php

class Dja_Db_Model_Field_OneToOne extends Dja_Db_Model_Field_ForeignKey implements Dja_Db_Model_Field_SingleRelationInterface
{
    protected function _setupBackwardsRelation()
    {
        $ownerClass = $this->getOption('ownerClass');
        $remoteClass = $this->getOption('relationClass');
        $related_name = ($this->related_name !== null) ? $this->related_name : $this->name;
        if (!isset($remoteClass::metadata()->{$related_name})) {
            $options = array(
                'Dja_Db_Model_Field_OneToOne',
                'relationClass' => $ownerClass,
            );
            $remoteClass::metadata()->addField($related_name, $options);
        }
    }
}