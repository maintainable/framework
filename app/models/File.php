<?php

class File
{
    public $id;
    public $name;

    // relationships and validation
    public function __construct($id, $name)
    {
        $this->id   = $id;
        $this->name = $name;
    }
}
