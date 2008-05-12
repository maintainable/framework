<?php
$map->connect('', array('controller' => 'home',
                        'action' => 'index'));

// ErrorController
$map->connect('error/page_not_found', array(
              'controller' => 'error',
              'action'     => 'page_not_found'));
$map->connect('error/:exception', array(
              'controller' => 'error',
              'action'     => 'index',
              'exception'  => null
             ));

// default
$map->connect(':controller/:action/:id', array('id' => null));
