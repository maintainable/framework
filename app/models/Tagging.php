<?php

class Tagging extends Mad_Model_Base
{
    // relationships and validation
    protected function _initialize()
    {
        $this->belongsTo('Tag');
        $this->belongsTo('Document');
    }
}
