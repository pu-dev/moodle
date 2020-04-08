<?php namespace mod_schedule;
defined('MOODLE_INTERNAL') || die(__FILE__);

require_once(dirname(__FILE__).'/../../../../config.php');
require_once($CFG->dirroot.'/mod/schedule/views/impl/view_student_base_impl.php');


class view_student_new_lesson_impl extends view_student_base_impl {
    public function __construct() {
        parent::__construct(student_class_tabs::TAB_NEW_LESSON);
    }

    protected function display() {
        parent::display();
        echo "To be implemented";
    }
}
