<?php namespace mod_schedule;
require_once(dirname(__FILE__).'/../../config.php');

function debug($msg) {
    global $CFG;
    
    // if ($CFG->debugdeveloper) {
        my_log($msg);
    // }
}


function my_log($log_msg)
{
    global $USER;
    if (is_null($log_msg) || isset($log_msg)) {
        $log_msg = 'EMPTY MSG';
    }
    $time_stamp = date('Y-M-d H:i:s');
    $log_msg = "[{$time_stamp} {$USER->username}] {$log_msg}";
    $log_filename = "/var/log/moodle_plugin.log";
    $log_file_data = $log_filename.'/log_' . date('d-M-Y') . '.log';
    // if you don't add `FILE_APPEND`, the file will be erased each time you add a log
    file_put_contents($log_filename, $log_msg . "\n", FILE_APPEND);
} 