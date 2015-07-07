<?php

require_once dirname(__FILE__) . '/config.php';
if(IS_DEBUG === true){
    ini_set('display_errors', 'On');
    ini_set('error_reporting', '2047');
}else{
    ini_set('display_errors', 'Off');
}

header("Content-Type: text/html; charset=".ENCODING_DEFIS);

require_once DOCUMENT_ROOT.'/classes/db.php';
DB::getInstance();

require_once DOCUMENT_ROOT.'/classes/city.php';
$city = new City;
$city -> write();
