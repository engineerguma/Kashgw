<?php

ini_set('display_errors',1);
#ini_set('error_reporting', E_ALL);

require 'library/settings.php';
require 'config.php';


spl_autoload_register(function ($class) {
    include LIBS . $class . '.php';
});

$app = new Bootstrap();
?>
