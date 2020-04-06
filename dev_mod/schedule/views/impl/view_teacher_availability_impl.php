<?php
require_once(dirname(__FILE__).'/../../../../config.php');
require_once($CFG->dirroot.'/mod/schedule/tools.php');
require_once($CFG->dirroot.'/mod/schedule/debug.php');
require_once($CFG->dirroot.'/mod/schedule/views/impl/view_teacher_base_impl.php');
require_once($CFG->dirroot.'/mod/schedule/components/teacher_availability_form.php');
require_once($CFG->dirroot.'/mod/schedule/components/teacher_availability_form_handler.php');
require_once($CFG->dirroot.'/mod/schedule/components/teacher_class_list.php');


class view_teacher_availability_impl extends view_teacher_base_impl {
    public function __construct() {
        parent::__construct(
            mod_schedule_teacher_class_tabs::TAB_AVAILABILITY_LESSON
        );
    }

    protected function display() {
        parent::display();
        $this->display_teacher_form();
        $this->display_teacher_class();
    }

    private function display_teacher_form() {
        $form_params = array('id' => $this->cm->id);
        $url_params = $form_params;

        $url = mod_schedule_tools::get_self_url($url_params);
        debug("Form target url: {$url}");

        $teacher_form = new mod_schedule_teacher_availability_form($url, $form_params);
        $teacher_form_handler = new mod_schedule_teacher_availability_form_handler($teacher_form);

        $teacher_form_handler->process_form();
        $teacher_form->display();
    }

    private function display_teacher_class() {

        $class_list = new mod_schedule_teacher_class_list();
        echo html_writer::table($class_list->get_class_table());
    }

}
