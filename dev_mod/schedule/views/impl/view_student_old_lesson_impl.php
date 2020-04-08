<?php namespace mod_schedule;
defined('MOODLE_INTERNAL') || die(__FILE__);

require_once(dirname(__FILE__).'/../../inc.php');
mod_require_once('/components/student_old_lesson_list.php');
mod_require_once('/views/impl/view_student_base_impl.php');


class view_student_old_lesson_impl extends view_student_base_impl {
    public function __construct() {
        parent::__construct(student_class_tabs::TAB_OLD_LESSON);
    }

    protected function render() {
        $html = parent::render();
        $html .= $this->render_old_lesson();
        return new view_result_html($html);
    }

    private function render_old_lesson() {
        $lesson_list = new student_old_lesson_list($this->cm);
        return \html_writer::table($lesson_list->get_class_table());
    }
}
