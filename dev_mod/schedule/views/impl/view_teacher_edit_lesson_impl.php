<?php namespace mod_schedule;
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../../inc.php');
mod_require_once('/views/impl/view_edit_lesson_base_impl.php');
mod_require_once('/components/teacher_edit_lesson_form.php');
mod_require_once('/components/teacher_edit_lesson_form_handler.php');


class view_teacher_edit_lesson_impl extends view_edit_lesson_base_impl {
    public function __construct() {
        $class_form = 'teacher_edit_lesson_form';
        $class_form_handler = 'teacher_edit_lesson_form_handler';

        parent::__construct(
            $class_form, 
            $class_form_handler);
    }
}
