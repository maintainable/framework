<?php

class SpecialTestHelper extends ApplicationHelper
{
    /**
     * Test method to make sure helpers work correctly
     */
    public function upper($val)
    {
        return strtoupper($val);
    }
}
