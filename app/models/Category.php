<?php

class Category extends Mad_Model_Base
{
    // relationships and validation
    protected function _initialize()
    {
        $this->hasAndBelongsToMany('Articles');

        $this->belongsTo('Category', array('foreignKey' => 'parent_id'));
        $this->hasMany('Categories', array('foreignKey' => 'parent_id'));
    }
}
