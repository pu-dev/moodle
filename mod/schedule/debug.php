<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);


function log_error($msg) {
    error_log($msg);
}

function log_info($msg) {
    error_log($msg);    
}

function log_debug($msg) {
    error_log($msg);    
}
