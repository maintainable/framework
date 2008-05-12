<?php

// Bootstrap the Rails environment, frameworks, and default configuration
require_once('boot.php');

$config = Mad_Madness_Initializer::run();

    // cache table structure to /tmp/cache/tables
    $config->model->cacheTables = true;

    // Settings in config/environments/* take precedence those specified above
    require_once 'environments/'.MAD_ENV.'.php';

$config->end();