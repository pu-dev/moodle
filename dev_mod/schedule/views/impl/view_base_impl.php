<?php
require_once(dirname(__FILE__).'/../../../../config.php');
// require_once($CFG->dirroot.'/mod/schedule/components/student_class_tabs.php');


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

        $my_url = mod_schedule_tools::get_self_url();
        $page_url = new moodle_url(
            $my_url,
            array('id' => $cm->id));


        $PAGE->set_url($page_url);
        $PAGE->set_title($schedule->name);
        $PAGE->set_heading($course->fullname);

        echo $OUTPUT->header();
        echo $OUTPUT->heading(format_string($schedule->name), 2, null);
        $this->display();
        echo $OUTPUT->footer();       
    }

    abstract protected function display();
}

