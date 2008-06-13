<?php

class User extends Mad_Model_Base
{
    // relationships and validation
    protected function _initialize()
    {
        $this->belongsTo('Company');
        $this->hasMany('Articles');
        $this->hasMany('Comments');
        $this->hasOne('Avatar', array('include'   => 'User', 
                                      'dependent' => 'destroy'));
    }
}
