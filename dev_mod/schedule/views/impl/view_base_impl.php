<?php
require_once(dirname(__FILE__).'/../../../../config.php');
require_once($CFG->dirroot.'/mod/schedule/debug.php');



abstract class view_base_impl {

    public function __construct() {
        global $DB, $PAGE, $OUTPUT;

        $cmid = required_param('id', PARAM_INT);

        // Get course module obj
        //
        if (! $cm = get_coursemodule_from_id('schedule', $cmid)) {
            print_error('invalidcoursemodule');
        }

        $this->cm = $cm;

        // Get course obj
        //
        if (! $course = $DB->get_record("course", array("id" => $cm->course))) {
            print_error('coursemisconf');
        }

        require_login($course, true, $cm);
        // require_course_login($course, false, $cm);

        if (!$schedule = schedule_get_schedule($cm->instance)) {
            print_error('invalidcoursemodule');
        }

        $url_params = array('id' => $cm->id);
        $url_self = mod_schedule_tools::get_self_url($url_params);
        debug("Set self url for the page: $url_self");

        $PAGE->set_url($url_self);
        $PAGE->set_title($schedule->name);
        $PAGE->set_heading($course->fullname);

        echo $OUTPUT->header();
        echo $OUTPUT->heading(format_string($schedule->name), 2, null);
        $this->display();
        echo $OUTPUT->footer();       
    }

    protected function alert_success($msg) {
        global $OUTPUT;
        echo $OUTPUT->notification($msg, "success");
    }
    
    protected function alert_error($msg) {
        global $OUTPUT;
        echo $OUTPUT->notification($msg, "error");
    }

    abstract protected function display();
}

