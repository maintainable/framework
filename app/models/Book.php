<?php

class Book extends Mad_Support_Object
{
    protected $_id          = null;
    protected $_hidden      = null;
    protected $_title       = null;
    protected $_comments    = null;
    protected $_author_name = null;
    protected $_theAuthor   = null;

    // Model file that doesn't inherit from Mad_Model_Base
    public function __construct($id, $title, $authorName)
    {
        $this->attrReader('id');
        $this->attrWriter('hidden');
        $this->attrAccessor('title', 'comments', 'no_property');

        // test using underscore/camelcase
        $this->attrAccessor('author_name', 'theAuthor');

        $this->_id      = $id;
        $this->title    = $title;
        $this->comments = 'empty';
    }


    public function getComments()
    {
        return '"'.$this->_comments.'"';
    }
    public function setComments($comments)
    {
        $comments = str_replace('"', '', $comments);
        $this->_comments = preg_replace('/<.*?>/', '', $comments);
    }

    // get/set by underscored attribute
    public function getAuthorName()
    {
        return ucfirst($this->_author_name);
    }
    public function setAuthorName($authorName)
    {
        $this->_author_name = trim($authorName);
    }

    // get/set by camelcase attribute
    public function getTheAuthor()
    {
        return ucfirst($this->_theAuthor);
    }
    public function setTheAuthor($authorName)
    {
        $this->_theAuthor = trim($authorName);
    }


    public static function find($type) 
    {
        if ($type == 'first') {
            return new Book(1, '1984', 'george orwell');
        } else {
            return array(new Book(1, '1984', 'george orwell'), 
                         new Book(2, 'The Hobbit', 'j.r. tolkien'));
        }
    }
}
