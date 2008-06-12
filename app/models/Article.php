<?php

class Article extends Mad_Model_Base
{
    // relationships and validation
    protected function _initialize()
    {
        $this->belongsTo('User', array('include' => 'Comments'));
        $this->hasMany('Comments');
        $this->hasAndBelongsToMany('Categories');
        $this->hasMany('Tags', array('through' => 'Taggings'));

        $this->hasMany('Fax_Attachments');
        $this->hasMany('Fax_Jobs', array('through' => 'Fax_Attachments'));
    }
    
    public function foo()
    {
        return 'test serializer foo';
    }
    
    public function bar()
    {
        return 'test serializer bar';
    }
}
