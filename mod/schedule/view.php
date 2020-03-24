<?php

require_once("../../config.php");
require_once("lib.php");
require_once($CFG->libdir . '/completionlib.php');

$id         = required_param('id', PARAM_INT);                 // Course Module ID
$action     = optional_param('action', '', PARAM_ALPHANUMEXT);
$attemptids = optional_param_array('attemptid', array(), PARAM_INT); // Get array of responses to delete or modify.
$userids    = optional_param_array('userid', array(), PARAM_INT); // Get array of users whose schedules need to be modified.
$notify     = optional_param('notify', '', PARAM_ALPHA);

$url = new moodle_url('/mod/schedule/view.php', array('id'=>$id));
if ($action !== '') {
    $url->param('action', $action);
}
$PAGE->set_url($url);

if (! $cm = get_coursemodule_from_id('schedule', $id)) {
    print_error('invalidcoursemodule');
}

if (! $course = $DB->get_record("course", array("id" => $cm->course))) {
    print_error('coursemisconf');
}

require_course_login($course, false, $cm);

if (!$schedule = schedule_get_schedule($cm->instance)) {
    print_error('invalidcoursemodule');
}

$strschedule = get_string('modulename', 'schedule');
$strschedules = get_string('modulenameplural', 'schedule');

$context = context_module::instance($cm->id);

list($scheduleavailable, $warnings) = schedule_get_availability_status($schedule);

if ($action == 'delschedule' and confirm_sesskey() and is_enrolled($context, NULL, 'mod/schedule:choose') and $schedule->allowupdate
        and $scheduleavailable) {
    $answercount = $DB->count_records('schedule_answers', array('scheduleid' => $schedule->id, 'userid' => $USER->id));
    if ($answercount > 0) {
        $scheduleanswers = $DB->get_records('schedule_answers', array('scheduleid' => $schedule->id, 'userid' => $USER->id),
            '', 'id');
        $todelete = array_keys($scheduleanswers);
        schedule_delete_responses($todelete, $schedule, $cm, $course);
        redirect("view.php?id=$cm->id");
    }
}

$PAGE->set_title($schedule->name);
$PAGE->set_heading($course->fullname);

/// Submit any new data if there is any
if (data_submitted() && !empty($action) && confirm_sesskey()) {
    $timenow = time();
    if (has_capability('mod/schedule:deleteresponses', $context)) {
        if ($action === 'delete') {
            // Some responses need to be deleted.
            schedule_delete_responses($attemptids, $schedule, $cm, $course);
            redirect("view.php?id=$cm->id");
        }
        if (preg_match('/^choose_(\d+)$/', $action, $actionmatch)) {
            // Modify responses of other users.
            $newoptionid = (int)$actionmatch[1];
            schedule_modify_responses($userids, $attemptids, $newoptionid, $schedule, $cm, $course);
            redirect("view.php?id=$cm->id");
        }
    }

    // Redirection after all POSTs breaks block editing, we need to be more specific!
    if ($schedule->allowmultiple) {
        $answer = optional_param_array('answer', array(), PARAM_INT);
    } else {
        $answer = optional_param('answer', '', PARAM_INT);
    }

    if (!$scheduleavailable) {
        $reason = current(array_keys($warnings));
        throw new moodle_exception($reason, 'schedule', '', $warnings[$reason]);
    }

    if ($answer && is_enrolled($context, null, 'mod/schedule:choose')) {
        schedule_user_submit_response($answer, $schedule, $USER->id, $course, $cm);
        redirect(new moodle_url('/mod/schedule/view.php',
            array('id' => $cm->id, 'notify' => 'schedulesaved', 'sesskey' => sesskey())));
    } else if (empty($answer) and $action === 'makeschedule') {
        // We cannot use the 'makeschedule' alone because there might be some legacy renderers without it,
        // outdated renderers will not get the 'mustchoose' message - bad luck.
        redirect(new moodle_url('/mod/schedule/view.php',
            array('id' => $cm->id, 'notify' => 'mustchooseone', 'sesskey' => sesskey())));
    }
}

// Completion and trigger events.
schedule_view($schedule, $course, $cm, $context);

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($schedule->name), 2, null);

if ($notify and confirm_sesskey()) {
    if ($notify === 'schedulesaved') {
        echo $OUTPUT->notification(get_string('schedulesaved', 'schedule'), 'notifysuccess');
    } else if ($notify === 'mustchooseone') {
        echo $OUTPUT->notification(get_string('mustchooseone', 'schedule'), 'notifyproblem');
    }
}

/// Display the schedule and possibly results
$eventdata = array();
$eventdata['objectid'] = $schedule->id;
$eventdata['context'] = $context;

/// Check to see if groups are being used in this schedule
$groupmode = groups_get_activity_groupmode($cm);

if ($groupmode) {
    groups_get_activity_group($cm, true);
    groups_print_activity_menu($cm, $CFG->wwwroot . '/mod/schedule/view.php?id='.$id);
}

// Check if we want to include responses from inactive users.
$onlyactive = $schedule->includeinactive ? false : true;

$allresponses = schedule_get_response_data($schedule, $cm, $groupmode, $onlyactive);   // Big function, approx 6 SQL calls per user.


if (has_capability('mod/schedule:readresponses', $context)) {
    schedule_show_reportlink($allresponses, $cm);
}

echo '<div class="clearer"></div>';

if ($schedule->intro) {
    echo $OUTPUT->box(format_module_intro('schedule', $schedule, $cm->id), 'generalbox', 'intro');
}

$timenow = time();
$current = schedule_get_my_response($schedule);
//if user has already made a selection, and they are not allowed to update it or if schedule is not open, show their selected answer.
if (isloggedin() && (!empty($current)) &&
    (empty($schedule->allowupdate) || ($timenow > $schedule->timeclose)) ) {
    $scheduletexts = array();
    foreach ($current as $c) {
        $scheduletexts[] = format_string(schedule_get_option_text($schedule, $c->optionid));
    }
    echo $OUTPUT->box(get_string("yourselection", "schedule", userdate($schedule->timeopen)).": ".implode('; ', $scheduletexts), 'generalbox', 'yourselection');
}

/// Print the form
$scheduleopen = true;
if ((!empty($schedule->timeopen)) && ($schedule->timeopen > $timenow)) {
    if ($schedule->showpreview) {
        echo $OUTPUT->box(get_string('previewonly', 'schedule', userdate($schedule->timeopen)), 'generalbox alert');
    } else {
        echo $OUTPUT->box(get_string("notopenyet", "schedule", userdate($schedule->timeopen)), "generalbox notopenyet");
        echo $OUTPUT->footer();
        exit;
    }
} else if ((!empty($schedule->timeclose)) && ($timenow > $schedule->timeclose)) {
    echo $OUTPUT->box(get_string("expired", "schedule", userdate($schedule->timeclose)), "generalbox expired");
    $scheduleopen = false;
}

if ( (!$current or $schedule->allowupdate) and $scheduleopen and is_enrolled($context, NULL, 'mod/schedule:choose')) {

    // Show information on how the results will be published to students.
    $publishinfo = null;
    switch ($schedule->showresults) {
        case SCHEDULE_SHOWRESULTS_NOT:
            $publishinfo = get_string('publishinfonever', 'schedule');
            break;

        case SCHEDULE_SHOWRESULTS_AFTER_ANSWER:
            if ($schedule->publish == SCHEDULE_PUBLISH_ANONYMOUS) {
                $publishinfo = get_string('publishinfoanonafter', 'schedule');
            } else {
                $publishinfo = get_string('publishinfofullafter', 'schedule');
            }
            break;

        case SCHEDULE_SHOWRESULTS_AFTER_CLOSE:
            if ($schedule->publish == SCHEDULE_PUBLISH_ANONYMOUS) {
                $publishinfo = get_string('publishinfoanonclose', 'schedule');
            } else {
                $publishinfo = get_string('publishinfofullclose', 'schedule');
            }
            break;

        default:
            // No need to inform the user in the case of SCHEDULE_SHOWRESULTS_ALWAYS since it's already obvious that the results are
            // being published.
            break;
    }

    // Show info if necessary.
    if (!empty($publishinfo)) {
        echo $OUTPUT->notification($publishinfo, 'info');
    }

    // They haven't made their schedule yet or updates allowed and schedule is open.
    $options = schedule_prepare_options($schedule, $USER, $cm, $allresponses);
    $renderer = $PAGE->get_renderer('mod_schedule');
    echo $renderer->display_options($options, $cm->id, $schedule->display, $schedule->allowmultiple);
    $scheduleformshown = true;
} else {
    $scheduleformshown = false;
}

if (!$scheduleformshown) {
    $sitecontext = context_system::instance();

    if (isguestuser()) {
        // Guest account
        echo $OUTPUT->confirm(get_string('noguestchoose', 'schedule').'<br /><br />'.get_string('liketologin'),
                     get_login_url(), new moodle_url('/course/view.php', array('id'=>$course->id)));
    } else if (!is_enrolled($context)) {
        // Only people enrolled can make a schedule
        $SESSION->wantsurl = qualified_me();
        $SESSION->enrolcancel = get_local_referer(false);

        $coursecontext = context_course::instance($course->id);
        $courseshortname = format_string($course->shortname, true, array('context' => $coursecontext));

        echo $OUTPUT->box_start('generalbox', 'notice');
        echo '<p align="center">'. get_string('notenrolledchoose', 'schedule') .'</p>';
        echo $OUTPUT->container_start('continuebutton');
        echo $OUTPUT->single_button(new moodle_url('/enrol/index.php?', array('id'=>$course->id)), get_string('enrolme', 'core_enrol', $courseshortname));
        echo $OUTPUT->container_end();
        echo $OUTPUT->box_end();

    }
}

// print the results at the bottom of the screen
if (schedule_can_view_results($schedule, $current, $scheduleopen)) {
    $results = prepare_schedule_show_results($schedule, $course, $cm, $allresponses);
    $renderer = $PAGE->get_renderer('mod_schedule');
    $resultstable = $renderer->display_result($results);
    echo $OUTPUT->box($resultstable);

} else if (!$scheduleformshown) {
    echo $OUTPUT->box(get_string('noresultsviewable', 'schedule'));
}

echo $OUTPUT->footer();
