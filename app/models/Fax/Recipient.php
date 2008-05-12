<?php

class Fax_Recipient extends Mad_Model_Base
{
    // relationships and validation
    protected function _initialize()
    {
        $this->belongsTo('Fax_Job');
    }
}
