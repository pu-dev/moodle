<?php
require_once(dirname(__FILE__).'/../../../../config.php');
require_once($CFG->dirroot.'/mod/schedule/views/impl/view_student_base_impl.php');


class view_student_view_lesson_impl extends view_student_base_impl {
    public function __construct() {
        parent::__construct(
            mod_schedule_student_class_tabs::TAB_VIEW_LESSON
        );

    }

    protected function display() {
        parent::display();
        echo "view lesson";
    }
}
