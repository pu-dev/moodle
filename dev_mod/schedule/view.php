<?php
require_once('../../config.php');

// Get course module id
//
$cmid = required_param('id', PARAM_INT);

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

$page_url = new moodle_url('/mod/schedule/view.php', array('id' => $cm->id));


$PAGE->set_url($page_url);
$PAGE->set_title($schedule->name);
$PAGE->set_heading($course->fullname);

// If your script calls require_login (and most scripts have to) and you are 
// providing a course, or a module to your require login call then you will 
// not need to call set_context().
//
// $PAGE->set_context(context_system::instance());
// $PAGE->set_context(context_coursecat::instance($categoryid));
// $PAGE->set_context(context_course::instance($courseid));
// $PAGE->set_context(context_module::instance($moduleid));

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($schedule->name), 2, null);
echo $OUTPUT->footer();
