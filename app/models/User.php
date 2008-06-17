<?php

class User extends Mad_Model_Base
{
    protected $_isCool;

    // relationships and validation
    protected function _initialize()
    {
        $this->belongsTo('Company');
        $this->hasMany('Articles');
        $this->hasMany('Comments');
        $this->hasOne('Avatar', array('include'   => 'User', 
                                      'dependent' => 'destroy'));
                                      
        $this->attrAccessor('is_cool');
    }
    
    public function getIsCool()
    {
        return true;
    }
}
