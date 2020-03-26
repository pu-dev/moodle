<?php
require_once("../../config.php");

// Get course module id
//

$cmid = required_param('cmid', PARAM_INT);
$saved = optional_param('saved', -1, PARAM_BOOL);

// if ( $saved ) {
    // redirect(new moodle_url('/mod/schedule/edit.php', array('cmid' => $cmid)));
    // return;
// }

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
// ?     $url = new moodle_url('/mod/schedule/edit.php', array('id'=>$id));
$PAGE->set_url($page_url);
$PAGE->set_title($schedule->name);
$PAGE->set_heading($course->fullname);



echo $OUTPUT->header();
// echo $OUTPUT->heading(format_string($schedule->name), 2, null);

$renderer = $PAGE->get_renderer('mod_schedule');
echo $renderer->display_add_teacher_availability_form($cm->id);

echo $OUTPUT->footer();

