<?php namespace mod_schedule;
defined('MOODLE_INTERNAL') || die(__FILE__);

require_once(dirname(__FILE__).'/../../inc.php');
mod_require_once('/components/teacher_old_lesson_list.php');
mod_require_once('/views/impl/view_teacher_base_impl.php');


class view_teacher_old_lesson_impl extends view_teacher_base_impl {
    public function __construct() {
        parent::__construct(teacher_class_tabs::TAB_OLD_LESSON);
    }

    protected function render() {
        $html = parent::render();
        $html .= $this->render_old_lesson();
        return new view_result_html($html);
    }

    private function render_old_lesson() {
        $lesson_list = new teacher_old_lesson_list($this->cm);
        return \html_writer::table($lesson_list->get_class_table());
    }
}
