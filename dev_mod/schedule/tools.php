<?php namespace mod_schedule;
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../../config.php');
require_once($CFG->dirroot.'/mod/schedule/debug.php');


class tools {
    const MINUTES_IN_HOUR = 60;
    const SECONDS_IN_MINUTE = 60;

    public static function get_self_url($url_params=null) {
        $src_url = $_SERVER['PHP_SELF'];

        if ( preg_match('/\/mod\/schedule\/.*\.php/', $src_url, $match) == 0 ) {
            die("Problem with retrieving self url.");
        }

        $url = new \moodle_url($match[0], $url_params);
        return $url;
    }


    public static function get_module_url($url_postfix='', $url_params=array()) {
        $url = new \moodle_url(
            "/mod/schedule/" . $url_postfix,
            $url_params
        );
        return $url;
    }

    public static function get_cmid() {
        return required_param('id', PARAM_INT);

    }


    public static function is_student($cmid = null) {
        if (is_null($cmid)) {
            $cmid = self::get_cmid();
        }

        $context = \context_module::instance($cmid);
        return has_capability('mod/schedule:student', $context);
    }


    public static function is_teacher($cmid = null) {
        if (is_null($cmid)) {
            $cmid = self::get_cmid();
        }

        $context = \context_module::instance($cmid);
        return has_capability('mod/schedule:teacher', $context);
    }


    public static function is_editingteacher($cmid = null) {
        if (is_null($cmid)) {
            $cmid = self::get_cmid();
        }

        $context = \context_module::instance($cmid);
        return has_capability('mod/schedule:editingteacher', $context);
    }


    public static function is_manager($cmid = null) {
        if (is_null($cmid)) {
            $cmid = self::get_cmid();
        }
        $context = \context_module::instance($cmid);
        return has_capability('mod/schedule:manager', $context);
    }

    public static function epoch_to_date($seconds, $nobr=false) {
        $date = \userdate($seconds, '%a %d %b %Y');
        if ( $nobr ) {
            $date = "<nobr>{$date}</nobr>";
        }
        return $date;
    }


    public static function truncate($txt, $len) {
        return strlen($in) > $len ? substr($in, 0, $len)."..." : $in;
    }



    public static function get_duration($start_hour, $start_minute, $end_hour, $end_minute) {
        $duration = (
            ($end_hour - $start_hour) * self::MINUTES_IN_HOUR +
            ($end_minute - $start_minute)
        );
        $duration *= self::SECONDS_IN_MINUTE;

        return $duration;
    }

    public static function get_epoch_date($date, $hour, $minute) {
        debug('get epoch '." date:{$date}  h:{$hour}. m:{$minute}");
        $epoch_date = (
            $date +
            $hour * self::MINUTES_IN_HOUR * self::SECONDS_IN_MINUTE +
            $minute * self::SECONDS_IN_MINUTE
        );

        return $epoch_date;
    }
     
}
