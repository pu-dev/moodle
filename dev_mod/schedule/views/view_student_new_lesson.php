<?php namespace mod_schedule;

require_once(dirname(__FILE__).'/../../../config.php');
require_once($CFG->dirroot.'/mod/schedule/views/impl/view_student_new_lesson_impl.php');


new view_student_new_lesson_impl();