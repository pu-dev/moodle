<?php
require_once(dirname(__FILE__).'/../../../../config.php');
require_once($CFG->dirroot.'/mod/schedule/views/impl/view_teacher_base_impl.php');


class view_teacher_other_impl extends view_teacher_base_impl {
    public function __construct() {
        parent::__construct(
            mod_schedule_teacher_class_tabs::TAB_OTHER_LESSON
        );

    }

    protected function display() {
        parent::display();
        echo "view teacher other";
    }
}
