<?php

class Comment extends Mad_Model_Base
{
    // relationships and validation
    protected function _initialize()
    {
        $this->belongsTo('Article');
        $this->belongsTo('User');
    }
}
