<?php namespace mod_schedule;
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../inc.php');
mod_require_once('/components/edit_lesson_form_base.php');


class teacher_edit_lesson_form extends edit_lesson_form_base {
    public function definition() {
        parent::definition();
    }
}
