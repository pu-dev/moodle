<?php
require_once(dirname(__FILE__).'/../../../../config.php');
require_once($CFG->dirroot.'/mod/schedule/views/impl/view_base_impl.php');
require_once($CFG->dirroot.'/mod/schedule/components/teacher_class_tabs.php');


class view_teacher_base_impl extends view_base_impl {

    public function __construct($current_tab) {
        $this->current_tab = $current_tab;
        parent::__construct();
    }

    protected function display() {
        global $OUTPUT, $PAGE;

        $renderer = $PAGE->get_renderer('mod_schedule');
        $teacher_tabs = new mod_schedule_teacher_class_tabs(
            $this->cm->id,
            $this->current_tab
        );
        echo $renderer->render($teacher_tabs);
    }
}

