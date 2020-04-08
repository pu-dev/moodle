<?php namespace mod_schedule;

require_once('../../config.php');
require_once($CFG->dirroot.'/mod/schedule/tools.php');

// if ( mod_schedule_tools::is_manager() ) {
    // return;
// }


if ( tools::is_editingteacher() ) {
    require_once('./views/view_teacher_availability.php');
    return;
}

if ( tools::is_teacher() ) {
    require_once('./views/view_teacher_availability.php');
    return;
}

if ( tools::is_student() ) {
    require_once('./views/view_student_book_lesson.php');
    return;
}



