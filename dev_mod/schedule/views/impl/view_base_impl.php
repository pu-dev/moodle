<?php namespace mod_schedule;
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../../inc.php');
mod_require_once('/tools.php');
mod_require_once('/views/view_result_base.php');


abstract class view_base_impl {
    protected $cm;
    protected $schedule;

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

        if (! $this->schedule = schedule_get_schedule($cm->instance)) {
            print_error('invalidcoursemodule');
        }

        $url_params = array('id' => $cm->id);
        $url_self = tools::get_self_url($url_params);
        debug("Set self url for the page: $url_self");

        $PAGE->set_url($url_self);
        $PAGE->set_title($this->schedule->name);
        $PAGE->set_heading($course->fullname);
    }

    public function view() {
        global $OUTPUT;

        $view_result = $this->render();

        if ( is_null($view_result->redirect) ) {
            $html = '';
            $html .= $OUTPUT->header();
            $html .= $OUTPUT->heading(format_string($this->schedule->name), 2, null);
            $html .= $view_result->html;
            $html .= $OUTPUT->footer();
            return $html;
        }
        else {
            redirect($view_result->redirect);
        }
    }

    protected function alert_success($msg) {
        global $OUTPUT;
        return $OUTPUT->notification($msg, "success");
    }

    protected function alert_error($msg) {
        global $OUTPUT;
        return $OUTPUT->notification($msg, "error");
    }

    abstract protected function render();
}


