<?php

class Admin_UsersController extends ApplicationController
{
    // filters and common variables
    protected function _initialize()
    {
    }

    public function index()
    {
        $this->head('ok');
    }
}
