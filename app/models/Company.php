<?php

class Company extends Mad_Model_Base
{
    // relationships and validation
    protected function _initialize()
    {
        $this->hasMany('Users');
        $this->hasMany('Employees', array('className' => 'User'));
    }
    
    public function foo()
    {
        return 'test serializer foo';
    }

}
