<?php
defined('MOODLE_INTERNAL') || die(__FILE__);

require_once(dirname(__FILE__).'/inc.php');
\mod_schedule\mod_require_once('/components/student_class_tabs.php');
\mod_schedule\mod_require_once('/components/teacher_class_tabs.php');


class mod_schedule_renderer extends \plugin_renderer_base {

    public function render_student_class_tabs(\mod_schedule\student_class_tabs $view) {
        return print_tabs(
            $view->get_tabs(),
            $view->current_tab, 
            null, 
            null,
            true
        );
    }


    public function render_teacher_class_tabs(\mod_schedule\teacher_class_tabs $view) {
        return print_tabs(
            $view->get_tabs(),
            $view->current_tab, 
            null, 
            null,
            true
        );
    }
}
