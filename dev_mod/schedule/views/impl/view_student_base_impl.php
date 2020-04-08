<?php namespace mod_schedule;
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../../../../config.php');
require_once($CFG->dirroot.'/mod/schedule/views/impl/view_base_impl.php');
require_once($CFG->dirroot.'/mod/schedule/components/student_class_tabs.php');


abstract class view_student_base_impl extends view_base_impl {

    private $current_tab;

    public function __construct($current_tab) {
        $this->current_tab = $current_tab;
        parent::__construct();
    }

    protected function render() {
        global $OUTPUT, $PAGE;

        $renderer = $PAGE->get_renderer('mod_schedule');
        $student_tabs = new student_class_tabs(
            $this->cm->id,
            $this->current_tab
        );
        return $renderer->render($student_tabs);
    }
}

