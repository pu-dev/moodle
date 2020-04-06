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

    public static function get_cmid() {
        return required_param('id', PARAM_INT);

    }


    public static function is_student($cmid = null) {
        if (is_null($cmid)) {
            $cmid = self::get_cmid();
        }

        $context = context_module::instance($cmid);
        return has_capability('mod/schedule:student', $context);
    }


    public static function is_teacher($cmid = null) {
        if (is_null($cmid)) {
            $cmid = self::get_cmid();
        }

        $context = context_module::instance($cmid);
        return has_capability('mod/schedule:teacher', $context);
    }


    public static function is_editingteacher($cmid = null) {
        if (is_null($cmid)) {
            $cmid = self::get_cmid();
        }

        $context = context_module::instance($cmid);
        return has_capability('mod/schedule:editingteacher', $context);
    }


    public static function is_manager($cmid = null) {
        if (is_null($cmid)) {
            $cmid = self::get_cmid();
        }
        $context = context_module::instance($cmid);
        return has_capability('mod/schedule:manager', $context);
    }

}