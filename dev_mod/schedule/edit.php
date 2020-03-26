<?php
require_once("./debug.php");
require_once("../../config.php");

// require_once($CFG->libdir . '/completionlib.php');
// require_once("lib.php");

// $url = new moodle_url('/mod/schedule/edit.php', array('id'=>$id));
// $PAGE->set_url($url);

$cmid = optional_param('cmid', '', PARAM_INT);

$PAGE->set_title("title TODO");
$PAGE->set_heading("heading TODO");

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($schedule->name), 2, null);

$renderer = $PAGE->get_renderer('mod_schedule');
echo $renderer->display_add_teacher_availability_form($cmid);

echo $OUTPUT->footer();

