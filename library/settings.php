<?php

$conf = parse_ini_file("config/conf.ini",true);
//print_r($conf);die();
define('DB_TYPE'  ,$conf['datastore']['dtype']);

define('DB_HOST'  ,$conf['datastore']['dhost']);
define('DB_USER'  ,$conf['datastore']['dbuser']);
define('DB_PASS'  ,$conf['datastore']['dbpass']);
define('DB_NAME'  ,$conf['datastore']['dbname']);


define('LOG_DIR', 'systemlog/');
define('EXECUTION_LOG', 'systemlog/tmp/');

define('HASH_ALGO', 'sha256');

define('min_credit',$conf['limits']['credit_min']);
define('max_credit',$conf['limits']['credit_max']);
define('min_debit',$conf['limits']['debit_min']);
define('max_debit',$conf['limits']['debit_max']);
