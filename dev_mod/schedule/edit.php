<?php
require_once("../../config.php");
require_once('./components/class_list.php');


$cmid = required_param('cmid', PARAM_INT);


// Get course module obj
//
if (! $cm = get_coursemodule_from_id('schedule', $cmid)) {
    print_error('invalidcoursemodule');
}


// Get course obj
//
if (! $course = $DB->get_record("course", array("id" => $cm->course))) {
    print_error('coursemisconf');
}


require_login($course, true, $cm);
// require_course_login($course, false, $cm);


if (!$schedule = schedule_get_schedule($cm->instance)) {
    print_error('invalidcoursemodule');
}


$page_url = new moodle_url('/mod/schedule/edit.php', array('cmid' => $cm->id));
$PAGE->set_url($page_url);
$PAGE->set_title($schedule->name);
$PAGE->set_heading($course->fullname);


$renderer = $PAGE->get_renderer('mod_schedule');

echo $OUTPUT->header();

echo render_teacher_availability_form($renderer, $cm->id);
echo render_class_list($renderer);

echo $OUTPUT->footer();



// 
// Render functions
//

function render_class_list($renderer) {
    $list_classes = new mod_schedule_class_list();
    return $renderer->render($list_classes);

}

function render_teacher_availability_form($renderer, $cmid) {
    return $renderer->display_add_teacher_availability_form($cmid);
}
