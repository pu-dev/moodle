<?php namespace mod_schedule;

require_once('../../config.php');
require_once($CFG->dirroot.'/mod/schedule/tools.php');


function goto_view($view) {
    $cmid = required_param('id', PARAM_INT);
    $url_params = array('id'=>$cmid);
    $url = tools::get_module_url($view, $url_params);
    redirect($url);
}

if ( tools::is_editingteacher() ) {
    goto_view('/views/view_teacher_availability.php');
}

if ( tools::is_teacher() ) {
    goto_view('/views/view_teacher_availability.php');
}

if ( tools::is_student() ) {
    goto_view('/views/view_student_book_lesson.php');
}
