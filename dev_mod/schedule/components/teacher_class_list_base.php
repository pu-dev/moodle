<?php namespace mod_schedule;
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../inc.php');
mod_require_once('/components/class_list_base.php');

abstract class teacher_class_list_base extends class_list_base {
    protected function __construct($cm) {
        parent::__construct($cm);
    }

    protected function get_cell_teacher_action_edit_lesson($class) {
        $form_view = 'views/view_teacher_edit_lesson.php';

        return parent::get_cell_action_edit_lesson(
            $class,
            $form_view);
    }
}
