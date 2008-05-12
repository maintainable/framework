<?php

class UnitTestHelper extends ApplicationHelper
{

    /**
     * Test method to make sure helpers work correctly
     */
    public function mySubStringMethod()
    {
        return substr($this->testVar, 0, 4);
    }
}
