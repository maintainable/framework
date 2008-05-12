<?php

class Fax_Job extends Mad_Model_Base
{
    // relationships and validation
    protected function _initialize()
    {
        $this->hasMany('Fax_Recipients');
        $this->hasMany('Fax_Attachments');
        $this->hasMany('Articles', array('through' => 'Fax_Attachments'));
    }
}
