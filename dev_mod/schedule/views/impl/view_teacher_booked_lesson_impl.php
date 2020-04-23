<?php namespace mod_schedule;
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../../inc.php');
// mod_require_once('/views/impl/view_teacher_paginated_base_impl.php');
mod_require_once('/views/impl/view_teacher_base_impl.php');
mod_require_once('/components/teacher_booked_lesson_list.php');


// class view_teacher_booked_lesson_impl extends view_teacher_paginated_base_impl {
class view_teacher_booked_lesson_impl extends view_teacher_base_impl {
    public function __construct() {
        parent::__construct(teacher_class_tabs::TAB_BOOKED_LESSON);
    }

    protected function render() {
        $html = parent::render();
        $html .= $this->render_booked_lesson_list();
        return new view_result_html($html);
    }

    private function render_booked_lesson_list() {
        $lesson_list = new teacher_booked_lesson_list($this->cm);
        return \html_writer::table($lesson_list->get_class_table());
    }

}
