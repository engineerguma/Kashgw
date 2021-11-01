<?php

$conf = parse_ini_file("config/conf.ini",true);
//print_r($conf);die();
define('URL'  ,$conf['url_connect']['url']);


define('DB_TYPE'  ,$conf['datastore']['dtype']);

define('DB_HOST'  ,$conf['datastore']['dhost']);
define('DB_USER'  ,$conf['datastore']['dbuser']);
define('DB_PASS'  ,$conf['datastore']['dbpass']);
define('DB_NAME'  ,$conf['datastore']['dbname']);


define('CERT_PATH', $conf['security']['cert_store']);

define('LOG_DIR', 'systemlog/');
define('EXECUTION_LOG', 'systemlog/tmp/');

define('HASH_ALGO', 'sha256');

define('STATUS_MINUTES',$conf['limits']['status_check_minutes']);

define('AM_KEY',$conf['am']['key']);
define('SUBJECT',$conf['am']['subject']);
define('ISSUER',$conf['am']['issuer']);
define('ALGO',$conf['am']['algorithm']);

define('AM_BASE_URL',$conf['am']['base_url']);
define('AM_CLIENT_ID',$conf['am']['client_id']);
define('AM_SECRET',$conf['am']['client_secret']);
define('AM_GRANT_TYPE',$conf['am']['grant_type']);
define('AM_WD_PIN',$conf['am']['wd_pin']);

define('min_credit',$conf['limits']['credit_min']);
define('max_credit',$conf['limits']['credit_max']);
define('min_debit',$conf['limits']['debit_min']);
define('max_debit',$conf['limits']['debit_max']);

define('COUNTRY',$conf['limits']['country']);
define('CURRENCY',$conf['limits']['currency']);
