<?php

require 'afbApi.php';
require 'm_img.php';
require_once 'API.php';
require 'Util.php';
$settings = array();

// get host id = [machinename]
$host = php_uname('n');

// get settings
$ini = parse_ini_file('app.ini',true);

// build config
if (isset($ini[$host])) {
    // host specific values
    $settings = $ini[$host];
}
if (isset($ini['common'])) {
    // common values (duplicate keys are not overwritten)
    $settings += $ini['common'];
}


// Requests from the same server don't have a HTTP_ORIGIN header
if (!array_key_exists('HTTP_ORIGIN', $_SERVER)) {
    $_SERVER['HTTP_ORIGIN'] = $_SERVER['SERVER_NAME'];
}

try {
    $API = new afbApi($_SERVER['REQUEST_URI'], $_SERVER['HTTP_ORIGIN']);
    echo $API->processAPI();
} catch (Exception $e) {
    echo json_encode(Array('error' => $e->getMessage()));
}