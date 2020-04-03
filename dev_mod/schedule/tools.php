<?php
require_once(dirname(__FILE__).'/../../config.php');


class mod_schedule_tools {
 
    public static function get_self_url() {
        $src_url = $_SERVER['PHP_SELF'];

        if ( preg_match('/\/mod\/schedule\/.*\.php/', $src_url, $match) == 0 ) {
            die("Problem with retrieving self url.");
        }

        return $match[0];
    }


    public static function get_module_url() {
        global $CFG;
        // $src_url = $_SERVER['PHP_SELF'];

        // if ( preg_match('/mod\/schedule/', $src_url, $match) == 0 ) {
        //     die("Problem with retrieving module url.");
        // }

        return $CFG->wwwroot."/mod/schedule";
    }
}