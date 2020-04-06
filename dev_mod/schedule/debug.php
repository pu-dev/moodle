<?php
require_once(dirname(__FILE__).'/../../config.php');

// error_reporting(E_ALL);
// ini_set('display_errors', 1);


// function log_error($msg) {
//     error_log($msg);
// }

// function log_info($msg) {
//     error_log($msg);    
// }

// function log_debug($msg) {
//     error_log($msg);    
// }
function debug($msg) {
    global $CFG;
    
    if ($CFG->debugdeveloper) {
        // debugging($msg);
// 
        // error_log($msg);
        my_log($msg);
    }
}


function my_log($log_msg)
{
    $log_filename = "/tmp/moodle.log";
    if (!file_exists($log_filename)) 
    {
        // create directory/folder uploads.
        mkdir($log_filename, 0777, true);
    }
    $log_file_data = $log_filename.'/log_' . date('d-M-Y') . '.log';
    // if you don't add `FILE_APPEND`, the file will be erased each time you add a log
    file_put_contents($log_file_data, $log_msg . "\n", FILE_APPEND);
} 