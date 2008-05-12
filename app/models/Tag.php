<?php

class Tag extends Mad_Model_Base
{
    // relationships and validation
    protected function _initialize()
    {
        $this->hasMany('Taggings');
        $this->hasMany('Articles', array('through' => 'Taggings'));
    }
}
