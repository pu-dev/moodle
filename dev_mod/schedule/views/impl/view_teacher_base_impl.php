<?php namespace mod_schedule;
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../../inc.php');
mod_require_once('/views/impl/view_base_impl.php');
mod_require_once('/components/teacher_class_tabs.php');


abstract class view_teacher_base_impl extends view_base_impl {

    public function __construct($current_tab) {
        $this->current_tab = $current_tab;
        parent::__construct();
    }

    protected function render() {
        global $OUTPUT, $PAGE;

        $renderer = $PAGE->get_renderer('mod_schedule');
        $teacher_tabs = new teacher_class_tabs(
            $this->cm->id,
            $this->current_tab
        );
        return $renderer->render($teacher_tabs);
    }
}

