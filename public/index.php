<?php
require_once dirname(dirname(__FILE__)).'/config/environment.php';

$dispatcher = Mad_Controller_Dispatcher::getInstance();
$dispatcher->dispatch();
