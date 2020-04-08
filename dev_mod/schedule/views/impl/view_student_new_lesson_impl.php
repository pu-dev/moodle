<?php namespace mod_schedule;
defined('MOODLE_INTERNAL') || die(__FILE__);

require_once(dirname(__FILE__).'/../../inc.php');
mod_require_once('/components/student_new_lesson_list.php');
mod_require_once('/views/impl/view_student_base_impl.php');


class view_student_new_lesson_impl extends view_student_base_impl {
    public function __construct() {
        parent::__construct(student_class_tabs::TAB_NEW_LESSON);
    }

    protected function render() {
        $html = parent::render();
        $html .= $this->render_new_lesson();
        return new view_result_html($html);
    }

    private function render_new_lesson() {
        $lesson_list = new student_new_lesson_list($this->cm);
        return \html_writer::table($lesson_list->get_class_table());
    }
}
