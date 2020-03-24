<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package   mod_schedule
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/** @global int $SCHEDULE_COLUMN_HEIGHT */
global $SCHEDULE_COLUMN_HEIGHT;
$SCHEDULE_COLUMN_HEIGHT = 300;

/** @global int $SCHEDULE_COLUMN_WIDTH */
global $SCHEDULE_COLUMN_WIDTH;
$SCHEDULE_COLUMN_WIDTH = 300;

define('SCHEDULE_PUBLISH_ANONYMOUS', '0');
define('SCHEDULE_PUBLISH_NAMES',     '1');

define('SCHEDULE_SHOWRESULTS_NOT',          '0');
define('SCHEDULE_SHOWRESULTS_AFTER_ANSWER', '1');
define('SCHEDULE_SHOWRESULTS_AFTER_CLOSE',  '2');
define('SCHEDULE_SHOWRESULTS_ALWAYS',       '3');

define('SCHEDULE_DISPLAY_HORIZONTAL',  '0');
define('SCHEDULE_DISPLAY_VERTICAL',    '1');

define('SCHEDULE_EVENT_TYPE_OPEN', 'open');
define('SCHEDULE_EVENT_TYPE_CLOSE', 'close');

/** @global array $SCHEDULE_PUBLISH */
global $SCHEDULE_PUBLISH;
$SCHEDULE_PUBLISH = array (SCHEDULE_PUBLISH_ANONYMOUS  => get_string('publishanonymous', 'schedule'),
                         SCHEDULE_PUBLISH_NAMES      => get_string('publishnames', 'schedule'));

/** @global array $SCHEDULE_SHOWRESULTS */
global $SCHEDULE_SHOWRESULTS;
$SCHEDULE_SHOWRESULTS = array (SCHEDULE_SHOWRESULTS_NOT          => get_string('publishnot', 'schedule'),
                         SCHEDULE_SHOWRESULTS_AFTER_ANSWER => get_string('publishafteranswer', 'schedule'),
                         SCHEDULE_SHOWRESULTS_AFTER_CLOSE  => get_string('publishafterclose', 'schedule'),
                         SCHEDULE_SHOWRESULTS_ALWAYS       => get_string('publishalways', 'schedule'));

/** @global array $SCHEDULE_DISPLAY */
global $SCHEDULE_DISPLAY;
$SCHEDULE_DISPLAY = array (SCHEDULE_DISPLAY_HORIZONTAL   => get_string('displayhorizontal', 'schedule'),
                         SCHEDULE_DISPLAY_VERTICAL     => get_string('displayvertical','schedule'));

/// Standard functions /////////////////////////////////////////////////////////

/**
 * @global object
 * @param object $course
 * @param object $user
 * @param object $mod
 * @param object $schedule
 * @return object|null
 */
function schedule_user_outline($course, $user, $mod, $schedule) {
    global $DB;
    if ($answer = $DB->get_record('schedule_answers', array('scheduleid' => $schedule->id, 'userid' => $user->id))) {
        $result = new stdClass();
        $result->info = "'".format_string(schedule_get_option_text($schedule, $answer->optionid))."'";
        $result->time = $answer->timemodified;
        return $result;
    }
    return NULL;
}

/**
 * Callback for the "Complete" report - prints the activity summary for the given user
 *
 * @param object $course
 * @param object $user
 * @param object $mod
 * @param object $schedule
 */
function schedule_user_complete($course, $user, $mod, $schedule) {
    global $DB;
    if ($answers = $DB->get_records('schedule_answers', array("scheduleid" => $schedule->id, "userid" => $user->id))) {
        $info = [];
        foreach ($answers as $answer) {
            $info[] = "'" . format_string(schedule_get_option_text($schedule, $answer->optionid)) . "'";
        }
        core_collator::asort($info);
        echo get_string("answered", "schedule") . ": ". join(', ', $info) . ". " .
                get_string("updated", '', userdate($answer->timemodified));
    } else {
        print_string("notanswered", "schedule");
    }
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @global object
 * @param object $schedule
 * @return int
 */
function schedule_add_instance($schedule) {
    global $DB, $CFG;
    require_once($CFG->dirroot.'/mod/schedule/locallib.php');

    $schedule->timemodified = time();

    //insert answers
    $schedule->id = $DB->insert_record("schedule", $schedule);
    foreach ($schedule->option as $key => $value) {
        $value = trim($value);
        if (isset($value) && $value <> '') {
            $option = new stdClass();
            $option->text = $value;
            $option->scheduleid = $schedule->id;
            if (isset($schedule->limit[$key])) {
                $option->maxanswers = $schedule->limit[$key];
            }
            $option->timemodified = time();
            $DB->insert_record("schedule_options", $option);
        }
    }

    // Add calendar events if necessary.
    schedule_set_events($schedule);
    if (!empty($schedule->completionexpected)) {
        \core_completion\api::update_completion_date_event($schedule->coursemodule, 'schedule', $schedule->id,
                $schedule->completionexpected);
    }

    return $schedule->id;
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @global object
 * @param object $schedule
 * @return bool
 */
function schedule_update_instance($schedule) {
    global $DB, $CFG;
    require_once($CFG->dirroot.'/mod/schedule/locallib.php');

    $schedule->id = $schedule->instance;
    $schedule->timemodified = time();

    //update, delete or insert answers
    foreach ($schedule->option as $key => $value) {
        $value = trim($value);
        $option = new stdClass();
        $option->text = $value;
        $option->scheduleid = $schedule->id;
        if (isset($schedule->limit[$key])) {
            $option->maxanswers = $schedule->limit[$key];
        }
        $option->timemodified = time();
        if (isset($schedule->optionid[$key]) && !empty($schedule->optionid[$key])){//existing schedule record
            $option->id=$schedule->optionid[$key];
            if (isset($value) && $value <> '') {
                $DB->update_record("schedule_options", $option);
            } else {
                // Remove the empty (unused) option.
                $DB->delete_records("schedule_options", array("id" => $option->id));
                // Delete any answers associated with this option.
                $DB->delete_records("schedule_answers", array("scheduleid" => $schedule->id, "optionid" => $option->id));
            }
        } else {
            if (isset($value) && $value <> '') {
                $DB->insert_record("schedule_options", $option);
            }
        }
    }

    // Add calendar events if necessary.
    schedule_set_events($schedule);
    $completionexpected = (!empty($schedule->completionexpected)) ? $schedule->completionexpected : null;
    \core_completion\api::update_completion_date_event($schedule->coursemodule, 'schedule', $schedule->id, $completionexpected);

    return $DB->update_record('schedule', $schedule);

}

/**
 * @global object
 * @param object $schedule
 * @param object $user
 * @param object $coursemodule
 * @param array $allresponses
 * @return array
 */
function schedule_prepare_options($schedule, $user, $coursemodule, $allresponses) {
    global $DB;

    $cdisplay = array('options'=>array());

    $cdisplay['limitanswers'] = true;
    $context = context_module::instance($coursemodule->id);

    foreach ($schedule->option as $optionid => $text) {
        if (isset($text)) { //make sure there are no dud entries in the db with blank text values.
            $option = new stdClass;
            $option->attributes = new stdClass;
            $option->attributes->value = $optionid;
            $option->text = format_string($text);
            $option->maxanswers = $schedule->maxanswers[$optionid];
            $option->displaylayout = $schedule->display;

            if (isset($allresponses[$optionid])) {
                $option->countanswers = count($allresponses[$optionid]);
            } else {
                $option->countanswers = 0;
            }
            if ($DB->record_exists('schedule_answers', array('scheduleid' => $schedule->id, 'userid' => $user->id, 'optionid' => $optionid))) {
                $option->attributes->checked = true;
            }
            if ( $schedule->limitanswers && ($option->countanswers >= $option->maxanswers) && empty($option->attributes->checked)) {
                $option->attributes->disabled = true;
            }
            $cdisplay['options'][] = $option;
        }
    }

    $cdisplay['hascapability'] = is_enrolled($context, NULL, 'mod/schedule:choose'); //only enrolled users are allowed to make a schedule

    if ($schedule->allowupdate && $DB->record_exists('schedule_answers', array('scheduleid'=> $schedule->id, 'userid'=> $user->id))) {
        $cdisplay['allowupdate'] = true;
    }

    if ($schedule->showpreview && $schedule->timeopen > time()) {
        $cdisplay['previewonly'] = true;
    }

    return $cdisplay;
}

/**
 * Modifies responses of other users adding the option $newoptionid to them
 *
 * @param array $userids list of users to add option to (must be users without any answers yet)
 * @param array $answerids list of existing attempt ids of users (will be either appended or
 *      substituted with the newoptionid, depending on $schedule->allowmultiple)
 * @param int $newoptionid
 * @param stdClass $schedule schedule object, result of {@link schedule_get_schedule()}
 * @param stdClass $cm
 * @param stdClass $course
 */
function schedule_modify_responses($userids, $answerids, $newoptionid, $schedule, $cm, $course) {
    // Get all existing responses and the list of non-respondents.
    $groupmode = groups_get_activity_groupmode($cm);
    $onlyactive = $schedule->includeinactive ? false : true;
    $allresponses = schedule_get_response_data($schedule, $cm, $groupmode, $onlyactive);

    // Check that the option value is valid.
    if (!$newoptionid || !isset($schedule->option[$newoptionid])) {
        return;
    }

    // First add responses for users who did not make any schedule yet.
    foreach ($userids as $userid) {
        if (isset($allresponses[0][$userid])) {
            schedule_user_submit_response($newoptionid, $schedule, $userid, $course, $cm);
        }
    }

    // Create the list of all options already selected by each user.
    $optionsbyuser = []; // Mapping userid=>array of chosen schedule options.
    $usersbyanswer = []; // Mapping answerid=>userid (which answer belongs to each user).
    foreach ($allresponses as $optionid => $responses) {
        if ($optionid > 0) {
            foreach ($responses as $userid => $userresponse) {
                $optionsbyuser += [$userid => []];
                $optionsbyuser[$userid][] = $optionid;
                $usersbyanswer[$userresponse->answerid] = $userid;
            }
        }
    }

    // Go through the list of submitted attemptids and find which users answers need to be updated.
    foreach ($answerids as $answerid) {
        if (isset($usersbyanswer[$answerid])) {
            $userid = $usersbyanswer[$answerid];
            if (!in_array($newoptionid, $optionsbyuser[$userid])) {
                $options = $schedule->allowmultiple ?
                        array_merge($optionsbyuser[$userid], [$newoptionid]) : $newoptionid;
                schedule_user_submit_response($options, $schedule, $userid, $course, $cm);
            }
        }
    }
}

/**
 * Process user submitted answers for a schedule,
 * and either updating them or saving new answers.
 *
 * @param int|array $formanswer the id(s) of the user submitted schedule options.
 * @param object $schedule the selected schedule.
 * @param int $userid user identifier.
 * @param object $course current course.
 * @param object $cm course context.
 * @return void
 */
function schedule_user_submit_response($formanswer, $schedule, $userid, $course, $cm) {
    global $DB, $CFG, $USER;
    require_once($CFG->libdir.'/completionlib.php');

    $continueurl = new moodle_url('/mod/schedule/view.php', array('id' => $cm->id));

    if (empty($formanswer)) {
        print_error('atleastoneoption', 'schedule', $continueurl);
    }

    if (is_array($formanswer)) {
        if (!$schedule->allowmultiple) {
            print_error('multiplenotallowederror', 'schedule', $continueurl);
        }
        $formanswers = $formanswer;
    } else {
        $formanswers = array($formanswer);
    }

    $options = $DB->get_records('schedule_options', array('scheduleid' => $schedule->id), '', 'id');
    foreach ($formanswers as $key => $val) {
        if (!isset($options[$val])) {
            print_error('cannotsubmit', 'schedule', $continueurl);
        }
    }
    // Start lock to prevent synchronous access to the same data
    // before it's updated, if using limits.
    if ($schedule->limitanswers) {
        $timeout = 10;
        $locktype = 'mod_schedule_schedule_user_submit_response';
        // Limiting access to this schedule.
        $resouce = 'scheduleid:' . $schedule->id;
        $lockfactory = \core\lock\lock_config::get_lock_factory($locktype);

        // Opening the lock.
        $schedulelock = $lockfactory->get_lock($resouce, $timeout, MINSECS);
        if (!$schedulelock) {
            print_error('cannotsubmit', 'schedule', $continueurl);
        }
    }

    $current = $DB->get_records('schedule_answers', array('scheduleid' => $schedule->id, 'userid' => $userid));

    // Array containing [answerid => optionid] mapping.
    $existinganswers = array_map(function($answer) {
        return $answer->optionid;
    }, $current);

    $context = context_module::instance($cm->id);

    $schedulesexceeded = false;
    $countanswers = array();
    foreach ($formanswers as $val) {
        $countanswers[$val] = 0;
    }
    if($schedule->limitanswers) {
        // Find out whether groups are being used and enabled
        if (groups_get_activity_groupmode($cm) > 0) {
            $currentgroup = groups_get_activity_group($cm);
        } else {
            $currentgroup = 0;
        }

        list ($insql, $params) = $DB->get_in_or_equal($formanswers, SQL_PARAMS_NAMED);

        if($currentgroup) {
            // If groups are being used, retrieve responses only for users in
            // current group
            global $CFG;

            $params['groupid'] = $currentgroup;
            $sql = "SELECT ca.*
                      FROM {schedule_answers} ca
                INNER JOIN {groups_members} gm ON ca.userid=gm.userid
                     WHERE optionid $insql
                       AND gm.groupid= :groupid";
        } else {
            // Groups are not used, retrieve all answers for this option ID
            $sql = "SELECT ca.*
                      FROM {schedule_answers} ca
                     WHERE optionid $insql";
        }

        $answers = $DB->get_records_sql($sql, $params);
        if ($answers) {
            foreach ($answers as $a) { //only return enrolled users.
                if (is_enrolled($context, $a->userid, 'mod/schedule:choose')) {
                    $countanswers[$a->optionid]++;
                }
            }
        }

        foreach ($countanswers as $opt => $count) {
            // Ignore the user's existing answers when checking whether an answer count has been exceeded.
            // A user may wish to update their response with an additional schedule option and shouldn't be competing with themself!
            if (in_array($opt, $existinganswers)) {
                continue;
            }
            if ($count >= $schedule->maxanswers[$opt]) {
                $schedulesexceeded = true;
                break;
            }
        }
    }

    // Check the user hasn't exceeded the maximum selections for the schedule(s) they have selected.
    $answersnapshots = array();
    $deletedanswersnapshots = array();
    if (!($schedule->limitanswers && $schedulesexceeded)) {
        if ($current) {
            // Update an existing answer.
            foreach ($current as $c) {
                if (in_array($c->optionid, $formanswers)) {
                    $DB->set_field('schedule_answers', 'timemodified', time(), array('id' => $c->id));
                } else {
                    $deletedanswersnapshots[] = $c;
                    $DB->delete_records('schedule_answers', array('id' => $c->id));
                }
            }

            // Add new ones.
            foreach ($formanswers as $f) {
                if (!in_array($f, $existinganswers)) {
                    $newanswer = new stdClass();
                    $newanswer->optionid = $f;
                    $newanswer->scheduleid = $schedule->id;
                    $newanswer->userid = $userid;
                    $newanswer->timemodified = time();
                    $newanswer->id = $DB->insert_record("schedule_answers", $newanswer);
                    $answersnapshots[] = $newanswer;
                }
            }
        } else {
            // Add new answer.
            foreach ($formanswers as $answer) {
                $newanswer = new stdClass();
                $newanswer->scheduleid = $schedule->id;
                $newanswer->userid = $userid;
                $newanswer->optionid = $answer;
                $newanswer->timemodified = time();
                $newanswer->id = $DB->insert_record("schedule_answers", $newanswer);
                $answersnapshots[] = $newanswer;
            }

            // Update completion state
            $completion = new completion_info($course);
            if ($completion->is_enabled($cm) && $schedule->completionsubmit) {
                $completion->update_state($cm, COMPLETION_COMPLETE);
            }
        }
    } else {
        // This is a schedule with limited options, and one of the options selected has just run over its limit.
        $schedulelock->release();
        print_error('schedulefull', 'schedule', $continueurl);
    }

    // Release lock.
    if (isset($schedulelock)) {
        $schedulelock->release();
    }

    // Trigger events.
    foreach ($deletedanswersnapshots as $answer) {
        \mod_schedule\event\answer_deleted::create_from_object($answer, $schedule, $cm, $course)->trigger();
    }
    foreach ($answersnapshots as $answer) {
        \mod_schedule\event\answer_created::create_from_object($answer, $schedule, $cm, $course)->trigger();
    }
}

/**
 * @param array $user
 * @param object $cm
 * @return void Output is echo'd
 */
function schedule_show_reportlink($user, $cm) {
    $userschosen = array();
    foreach($user as $optionid => $userlist) {
        if ($optionid) {
            $userschosen = array_merge($userschosen, array_keys($userlist));
        }
    }
    $responsecount = count(array_unique($userschosen));

    echo '<div class="reportlink">';
    echo "<a href=\"report.php?id=$cm->id\">".get_string("viewallresponses", "schedule", $responsecount)."</a>";
    echo '</div>';
}

/**
 * @global object
 * @param object $schedule
 * @param object $course
 * @param object $coursemodule
 * @param array $allresponses

 *  * @param bool $allresponses
 * @return object
 */
function prepare_schedule_show_results($schedule, $course, $cm, $allresponses) {
    global $OUTPUT;

    $display = clone($schedule);
    $display->coursemoduleid = $cm->id;
    $display->courseid = $course->id;

    if (!empty($schedule->showunanswered)) {
        $schedule->option[0] = get_string('notanswered', 'schedule');
        $schedule->maxanswers[0] = 0;
    }

    // Remove from the list of non-respondents the users who do not have access to this activity.
    if (!empty($display->showunanswered) && $allresponses[0]) {
        $info = new \core_availability\info_module(cm_info::create($cm));
        $allresponses[0] = $info->filter_user_list($allresponses[0]);
    }

    //overwrite options value;
    $display->options = array();
    $allusers = [];
    foreach ($schedule->option as $optionid => $optiontext) {
        $display->options[$optionid] = new stdClass;
        $display->options[$optionid]->text = format_string($optiontext, true,
            ['context' => context_module::instance($cm->id)]);
        $display->options[$optionid]->maxanswer = $schedule->maxanswers[$optionid];

        if (array_key_exists($optionid, $allresponses)) {
            $display->options[$optionid]->user = $allresponses[$optionid];
            $allusers = array_merge($allusers, array_keys($allresponses[$optionid]));
        }
    }
    unset($display->option);
    unset($display->maxanswers);

    $display->numberofuser = count(array_unique($allusers));
    $context = context_module::instance($cm->id);
    $display->viewresponsecapability = has_capability('mod/schedule:readresponses', $context);
    $display->deleterepsonsecapability = has_capability('mod/schedule:deleteresponses',$context);
    $display->fullnamecapability = has_capability('moodle/site:viewfullnames', $context);

    if (empty($allresponses)) {
        echo $OUTPUT->heading(get_string("nousersyet"), 3, null);
        return false;
    }

    return $display;
}

/**
 * @global object
 * @param array $attemptids
 * @param object $schedule Schedule main table row
 * @param object $cm Course-module object
 * @param object $course Course object
 * @return bool
 */
function schedule_delete_responses($attemptids, $schedule, $cm, $course) {
    global $DB, $CFG, $USER;
    require_once($CFG->libdir.'/completionlib.php');

    if(!is_array($attemptids) || empty($attemptids)) {
        return false;
    }

    foreach($attemptids as $num => $attemptid) {
        if(empty($attemptid)) {
            unset($attemptids[$num]);
        }
    }

    $completion = new completion_info($course);
    foreach($attemptids as $attemptid) {
        if ($todelete = $DB->get_record('schedule_answers', array('scheduleid' => $schedule->id, 'id' => $attemptid))) {
            // Trigger the event answer deleted.
            \mod_schedule\event\answer_deleted::create_from_object($todelete, $schedule, $cm, $course)->trigger();
            $DB->delete_records('schedule_answers', array('scheduleid' => $schedule->id, 'id' => $attemptid));
        }
    }

    // Update completion state.
    if ($completion->is_enabled($cm) && $schedule->completionsubmit) {
        $completion->update_state($cm, COMPLETION_INCOMPLETE);
    }

    return true;
}


/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @global object
 * @param int $id
 * @return bool
 */
function schedule_delete_instance($id) {
    global $DB;

    if (! $schedule = $DB->get_record("schedule", array("id"=>"$id"))) {
        return false;
    }

    $result = true;

    if (! $DB->delete_records("schedule_answers", array("scheduleid"=>"$schedule->id"))) {
        $result = false;
    }

    if (! $DB->delete_records("schedule_options", array("scheduleid"=>"$schedule->id"))) {
        $result = false;
    }

    if (! $DB->delete_records("schedule", array("id"=>"$schedule->id"))) {
        $result = false;
    }
    // Remove old calendar events.
    if (! $DB->delete_records('event', array('modulename' => 'schedule', 'instance' => $schedule->id))) {
        $result = false;
    }

    return $result;
}

/**
 * Returns text string which is the answer that matches the id
 *
 * @global object
 * @param object $schedule
 * @param int $id
 * @return string
 */
function schedule_get_option_text($schedule, $id) {
    global $DB;

    if ($result = $DB->get_record("schedule_options", array("id" => $id))) {
        return $result->text;
    } else {
        return get_string("notanswered", "schedule");
    }
}

/**
 * Gets a full schedule record
 *
 * @global object
 * @param int $scheduleid
 * @return object|bool The schedule or false
 */
function schedule_get_schedule($scheduleid) {
    global $DB;

    if ($schedule = $DB->get_record("schedule", array("id" => $scheduleid))) {
        if ($options = $DB->get_records("schedule_options", array("scheduleid" => $scheduleid), "id")) {
            foreach ($options as $option) {
                $schedule->option[$option->id] = $option->text;
                $schedule->maxanswers[$option->id] = $option->maxanswers;
            }
            return $schedule;
        }
    }
    return false;
}

/**
 * List the actions that correspond to a view of this module.
 * This is used by the participation report.
 *
 * Note: This is not used by new logging system. Event with
 *       crud = 'r' and edulevel = LEVEL_PARTICIPATING will
 *       be considered as view action.
 *
 * @return array
 */
function schedule_get_view_actions() {
    return array('view','view all','report');
}

/**
 * List the actions that correspond to a post of this module.
 * This is used by the participation report.
 *
 * Note: This is not used by new logging system. Event with
 *       crud = ('c' || 'u' || 'd') and edulevel = LEVEL_PARTICIPATING
 *       will be considered as post action.
 *
 * @return array
 */
function schedule_get_post_actions() {
    return array('choose','choose again');
}


/**
 * Implementation of the function for printing the form elements that control
 * whether the course reset functionality affects the schedule.
 *
 * @param object $mform form passed by reference
 */
function schedule_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'scheduleheader', get_string('modulenameplural', 'schedule'));
    $mform->addElement('advcheckbox', 'reset_schedule', get_string('removeresponses','schedule'));
}

/**
 * Course reset form defaults.
 *
 * @return array
 */
function schedule_reset_course_form_defaults($course) {
    return array('reset_schedule'=>1);
}

/**
 * Actual implementation of the reset course functionality, delete all the
 * schedule responses for course $data->courseid.
 *
 * @global object
 * @global object
 * @param object $data the data submitted from the reset course.
 * @return array status array
 */
function schedule_reset_userdata($data) {
    global $CFG, $DB;

    $componentstr = get_string('modulenameplural', 'schedule');
    $status = array();

    if (!empty($data->reset_schedule)) {
        $schedulessql = "SELECT ch.id
                       FROM {schedule} ch
                       WHERE ch.course=?";

        $DB->delete_records_select('schedule_answers', "scheduleid IN ($schedulessql)", array($data->courseid));
        $status[] = array('component'=>$componentstr, 'item'=>get_string('removeresponses', 'schedule'), 'error'=>false);
    }

    /// updating dates - shift may be negative too
    if ($data->timeshift) {
        // Any changes to the list of dates that needs to be rolled should be same during course restore and course reset.
        // See MDL-9367.
        shift_course_mod_dates('schedule', array('timeopen', 'timeclose'), $data->timeshift, $data->courseid);
        $status[] = array('component'=>$componentstr, 'item'=>get_string('datechanged'), 'error'=>false);
    }

    return $status;
}

/**
 * @global object
 * @global object
 * @global object
 * @uses CONTEXT_MODULE
 * @param object $schedule
 * @param object $cm
 * @param int $groupmode
 * @param bool $onlyactive Whether to get response data for active users only.
 * @return array
 */
function schedule_get_response_data($schedule, $cm, $groupmode, $onlyactive) {
    global $CFG, $USER, $DB;

    $context = context_module::instance($cm->id);

/// Get the current group
    if ($groupmode > 0) {
        $currentgroup = groups_get_activity_group($cm);
    } else {
        $currentgroup = 0;
    }

/// Initialise the returned array, which is a matrix:  $allresponses[responseid][userid] = responseobject
    $allresponses = array();

/// First get all the users who have access here
/// To start with we assume they are all "unanswered" then move them later
    $extrafields = get_extra_user_fields($context);
    $allresponses[0] = get_enrolled_users($context, 'mod/schedule:choose', $currentgroup,
            user_picture::fields('u', $extrafields), null, 0, 0, $onlyactive);

/// Get all the recorded responses for this schedule
    $rawresponses = $DB->get_records('schedule_answers', array('scheduleid' => $schedule->id));

/// Use the responses to move users into the correct column

    if ($rawresponses) {
        $answeredusers = array();
        foreach ($rawresponses as $response) {
            if (isset($allresponses[0][$response->userid])) {   // This person is enrolled and in correct group
                $allresponses[0][$response->userid]->timemodified = $response->timemodified;
                $allresponses[$response->optionid][$response->userid] = clone($allresponses[0][$response->userid]);
                $allresponses[$response->optionid][$response->userid]->answerid = $response->id;
                $answeredusers[] = $response->userid;
            }
        }
        foreach ($answeredusers as $answereduser) {
            unset($allresponses[0][$answereduser]);
        }
    }
    return $allresponses;
}

/**
 * @uses FEATURE_GROUPS
 * @uses FEATURE_GROUPINGS
 * @uses FEATURE_MOD_INTRO
 * @uses FEATURE_COMPLETION_TRACKS_VIEWS
 * @uses FEATURE_GRADE_HAS_GRADE
 * @uses FEATURE_GRADE_OUTCOMES
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, null if doesn't know
 */
function schedule_supports($feature) {
    switch($feature) {
        case FEATURE_GROUPS:                  return true;
        case FEATURE_GROUPINGS:               return true;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_COMPLETION_HAS_RULES:    return true;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;

        default: return null;
    }
}

/**
 * Adds module specific settings to the settings block
 *
 * @param settings_navigation $settings The settings navigation object
 * @param navigation_node $schedulenode The node to add module settings to
 */
function schedule_extend_settings_navigation(settings_navigation $settings, navigation_node $schedulenode) {
    global $PAGE;

    if (has_capability('mod/schedule:readresponses', $PAGE->cm->context)) {

        $groupmode = groups_get_activity_groupmode($PAGE->cm);
        if ($groupmode) {
            groups_get_activity_group($PAGE->cm, true);
        }

        $schedule = schedule_get_schedule($PAGE->cm->instance);

        // Check if we want to include responses from inactive users.
        $onlyactive = $schedule->includeinactive ? false : true;

        // Big function, approx 6 SQL calls per user.
        $allresponses = schedule_get_response_data($schedule, $PAGE->cm, $groupmode, $onlyactive);

        $allusers = [];
        foreach($allresponses as $optionid => $userlist) {
            if ($optionid) {
                $allusers = array_merge($allusers, array_keys($userlist));
            }
        }
        $responsecount = count(array_unique($allusers));
        $schedulenode->add(get_string("viewallresponses", "schedule", $responsecount), new moodle_url('/mod/schedule/report.php', array('id'=>$PAGE->cm->id)));
    }
}

/**
 * Obtains the automatic completion state for this schedule based on any conditions
 * in forum settings.
 *
 * @param object $course Course
 * @param object $cm Course-module
 * @param int $userid User ID
 * @param bool $type Type of comparison (or/and; can be used as return value if no conditions)
 * @return bool True if completed, false if not, $type if conditions not set.
 */
function schedule_get_completion_state($course, $cm, $userid, $type) {
    global $CFG,$DB;

    // Get schedule details
    $schedule = $DB->get_record('schedule', array('id'=>$cm->instance), '*',
            MUST_EXIST);

    // If completion option is enabled, evaluate it and return true/false
    if($schedule->completionsubmit) {
        return $DB->record_exists('schedule_answers', array(
                'scheduleid'=>$schedule->id, 'userid'=>$userid));
    } else {
        // Completion option is not enabled so just return $type
        return $type;
    }
}

/**
 * Return a list of page types
 * @param string $pagetype current page type
 * @param stdClass $parentcontext Block's parent context
 * @param stdClass $currentcontext Current context of block
 */
function schedule_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $module_pagetype = array('mod-schedule-*'=>get_string('page-mod-schedule-x', 'schedule'));
    return $module_pagetype;
}

/**
 * @deprecated since Moodle 3.3, when the block_course_overview block was removed.
 */
function schedule_print_overview() {
    throw new coding_exception('schedule_print_overview() can not be used any more and is obsolete.');
}


/**
 * Get responses of a given user on a given schedule.
 *
 * @param stdClass $schedule Schedule record
 * @param int $userid User id
 * @return array of schedule answers records
 * @since  Moodle 3.6
 */
function schedule_get_user_response($schedule, $userid) {
    global $DB;
    return $DB->get_records('schedule_answers', array('scheduleid' => $schedule->id, 'userid' => $userid), 'optionid');
}

/**
 * Get my responses on a given schedule.
 *
 * @param stdClass $schedule Schedule record
 * @return array of schedule answers records
 * @since  Moodle 3.0
 */
function schedule_get_my_response($schedule) {
    global $USER;
    return schedule_get_user_response($schedule, $USER->id);
}


/**
 * Get all the responses on a given schedule.
 *
 * @param stdClass $schedule Schedule record
 * @return array of schedule answers records
 * @since  Moodle 3.0
 */
function schedule_get_all_responses($schedule) {
    global $DB;
    return $DB->get_records('schedule_answers', array('scheduleid' => $schedule->id));
}


/**
 * Return true if we are allowd to view the schedule results.
 *
 * @param stdClass $schedule Schedule record
 * @param rows|null $current my schedule responses
 * @param bool|null $scheduleopen if the schedule is open
 * @return bool true if we can view the results, false otherwise.
 * @since  Moodle 3.0
 */
function schedule_can_view_results($schedule, $current = null, $scheduleopen = null) {

    if (is_null($scheduleopen)) {
        $timenow = time();

        if ($schedule->timeopen != 0 && $timenow < $schedule->timeopen) {
            // If the schedule is not available, we can't see the results.
            return false;
        }

        if ($schedule->timeclose != 0 && $timenow > $schedule->timeclose) {
            $scheduleopen = false;
        } else {
            $scheduleopen = true;
        }
    }
    if (empty($current)) {
        $current = schedule_get_my_response($schedule);
    }

    if ($schedule->showresults == SCHEDULE_SHOWRESULTS_ALWAYS or
       ($schedule->showresults == SCHEDULE_SHOWRESULTS_AFTER_ANSWER and !empty($current)) or
       ($schedule->showresults == SCHEDULE_SHOWRESULTS_AFTER_CLOSE and !$scheduleopen)) {
        return true;
    }
    return false;
}

/**
 * Mark the activity completed (if required) and trigger the course_module_viewed event.
 *
 * @param  stdClass $schedule     schedule object
 * @param  stdClass $course     course object
 * @param  stdClass $cm         course module object
 * @param  stdClass $context    context object
 * @since Moodle 3.0
 */
function schedule_view($schedule, $course, $cm, $context) {

    // Trigger course_module_viewed event.
    $params = array(
        'context' => $context,
        'objectid' => $schedule->id
    );

    $event = \mod_schedule\event\course_module_viewed::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('schedule', $schedule);
    $event->trigger();

    // Completion.
    $completion = new completion_info($course);
    $completion->set_module_viewed($cm);
}

/**
 * Check if a schedule is available for the current user.
 *
 * @param  stdClass  $schedule            schedule record
 * @return array                       status (available or not and possible warnings)
 */
function schedule_get_availability_status($schedule) {
    $available = true;
    $warnings = array();

    $timenow = time();

    if (!empty($schedule->timeopen) && ($schedule->timeopen > $timenow)) {
        $available = false;
        $warnings['notopenyet'] = userdate($schedule->timeopen);
    } else if (!empty($schedule->timeclose) && ($timenow > $schedule->timeclose)) {
        $available = false;
        $warnings['expired'] = userdate($schedule->timeclose);
    }
    if (!$schedule->allowupdate && schedule_get_my_response($schedule)) {
        $available = false;
        $warnings['schedulesaved'] = '';
    }

    // Schedule is available.
    return array($available, $warnings);
}

/**
 * This standard function will check all instances of this module
 * and make sure there are up-to-date events created for each of them.
 * If courseid = 0, then every schedule event in the site is checked, else
 * only schedule events belonging to the course specified are checked.
 * This function is used, in its new format, by restore_refresh_events()
 *
 * @param int $courseid
 * @param int|stdClass $instance Schedule module instance or ID.
 * @param int|stdClass $cm Course module object or ID (not used in this module).
 * @return bool
 */
function schedule_refresh_events($courseid = 0, $instance = null, $cm = null) {
    global $DB, $CFG;
    require_once($CFG->dirroot.'/mod/schedule/locallib.php');

    // If we have instance information then we can just update the one event instead of updating all events.
    if (isset($instance)) {
        if (!is_object($instance)) {
            $instance = $DB->get_record('schedule', array('id' => $instance), '*', MUST_EXIST);
        }
        schedule_set_events($instance);
        return true;
    }

    if ($courseid) {
        if (! $schedules = $DB->get_records("schedule", array("course" => $courseid))) {
            return true;
        }
    } else {
        if (! $schedules = $DB->get_records("schedule")) {
            return true;
        }
    }

    foreach ($schedules as $schedule) {
        schedule_set_events($schedule);
    }
    return true;
}

/**
 * Check if the module has any update that affects the current user since a given time.
 *
 * @param  cm_info $cm course module data
 * @param  int $from the time to check updates from
 * @param  array $filter  if we need to check only specific updates
 * @return stdClass an object with the different type of areas indicating if they were updated or not
 * @since Moodle 3.2
 */
function schedule_check_updates_since(cm_info $cm, $from, $filter = array()) {
    global $DB;

    $updates = new stdClass();
    $schedule = $DB->get_record($cm->modname, array('id' => $cm->instance), '*', MUST_EXIST);
    list($available, $warnings) = schedule_get_availability_status($schedule);
    if (!$available) {
        return $updates;
    }

    $updates = course_check_module_updates_since($cm, $from, array(), $filter);

    if (!schedule_can_view_results($schedule)) {
        return $updates;
    }
    // Check if there are new responses in the schedule.
    $updates->answers = (object) array('updated' => false);
    $select = 'scheduleid = :id AND timemodified > :since';
    $params = array('id' => $schedule->id, 'since' => $from);
    $answers = $DB->get_records_select('schedule_answers', $select, $params, '', 'id');
    if (!empty($answers)) {
        $updates->answers->updated = true;
        $updates->answers->itemids = array_keys($answers);
    }

    return $updates;
}

/**
 * This function receives a calendar event and returns the action associated with it, or null if there is none.
 *
 * This is used by block_myoverview in order to display the event appropriately. If null is returned then the event
 * is not displayed on the block.
 *
 * @param calendar_event $event
 * @param \core_calendar\action_factory $factory
 * @param int $userid User id to use for all capability checks, etc. Set to 0 for current user (default).
 * @return \core_calendar\local\event\entities\action_interface|null
 */
function mod_schedule_core_calendar_provide_event_action(calendar_event $event,
                                                       \core_calendar\action_factory $factory,
                                                       int $userid = 0) {
    global $USER;

    if (!$userid) {
        $userid = $USER->id;
    }

    $cm = get_fast_modinfo($event->courseid, $userid)->instances['schedule'][$event->instance];

    if (!$cm->uservisible) {
        // The module is not visible to the user for any reason.
        return null;
    }

    $completion = new \completion_info($cm->get_course());

    $completiondata = $completion->get_data($cm, false, $userid);

    if ($completiondata->completionstate != COMPLETION_INCOMPLETE) {
        return null;
    }

    $now = time();

    if (!empty($cm->customdata['timeclose']) && $cm->customdata['timeclose'] < $now) {
        // The schedule has closed so the user can no longer submit anything.
        return null;
    }

    // The schedule is actionable if we don't have a start time or the start time is
    // in the past.
    $actionable = (empty($cm->customdata['timeopen']) || $cm->customdata['timeopen'] <= $now);

    if ($actionable && schedule_get_user_response((object)['id' => $event->instance], $userid)) {
        // There is no action if the user has already submitted their schedule.
        return null;
    }

    return $factory->create_instance(
        get_string('viewschedules', 'schedule'),
        new \moodle_url('/mod/schedule/view.php', array('id' => $cm->id)),
        1,
        $actionable
    );
}

/**
 * This function calculates the minimum and maximum cutoff values for the timestart of
 * the given event.
 *
 * It will return an array with two values, the first being the minimum cutoff value and
 * the second being the maximum cutoff value. Either or both values can be null, which
 * indicates there is no minimum or maximum, respectively.
 *
 * If a cutoff is required then the function must return an array containing the cutoff
 * timestamp and error string to display to the user if the cutoff value is violated.
 *
 * A minimum and maximum cutoff return value will look like:
 * [
 *     [1505704373, 'The date must be after this date'],
 *     [1506741172, 'The date must be before this date']
 * ]
 *
 * @param calendar_event $event The calendar event to get the time range for
 * @param stdClass $schedule The module instance to get the range from
 */
function mod_schedule_core_calendar_get_valid_event_timestart_range(\calendar_event $event, \stdClass $schedule) {
    $mindate = null;
    $maxdate = null;

    if ($event->eventtype == SCHEDULE_EVENT_TYPE_OPEN) {
        if (!empty($schedule->timeclose)) {
            $maxdate = [
                $schedule->timeclose,
                get_string('openafterclose', 'schedule')
            ];
        }
    } else if ($event->eventtype == SCHEDULE_EVENT_TYPE_CLOSE) {
        if (!empty($schedule->timeopen)) {
            $mindate = [
                $schedule->timeopen,
                get_string('closebeforeopen', 'schedule')
            ];
        }
    }

    return [$mindate, $maxdate];
}

/**
 * This function will update the schedule module according to the
 * event that has been modified.
 *
 * It will set the timeopen or timeclose value of the schedule instance
 * according to the type of event provided.
 *
 * @throws \moodle_exception
 * @param \calendar_event $event
 * @param stdClass $schedule The module instance to get the range from
 */
function mod_schedule_core_calendar_event_timestart_updated(\calendar_event $event, \stdClass $schedule) {
    global $DB;

    if (!in_array($event->eventtype, [SCHEDULE_EVENT_TYPE_OPEN, SCHEDULE_EVENT_TYPE_CLOSE])) {
        return;
    }

    $courseid = $event->courseid;
    $modulename = $event->modulename;
    $instanceid = $event->instance;
    $modified = false;

    // Something weird going on. The event is for a different module so
    // we should ignore it.
    if ($modulename != 'schedule') {
        return;
    }

    if ($schedule->id != $instanceid) {
        return;
    }

    $coursemodule = get_fast_modinfo($courseid)->instances[$modulename][$instanceid];
    $context = context_module::instance($coursemodule->id);

    // The user does not have the capability to modify this activity.
    if (!has_capability('moodle/course:manageactivities', $context)) {
        return;
    }

    if ($event->eventtype == SCHEDULE_EVENT_TYPE_OPEN) {
        // If the event is for the schedule activity opening then we should
        // set the start time of the schedule activity to be the new start
        // time of the event.
        if ($schedule->timeopen != $event->timestart) {
            $schedule->timeopen = $event->timestart;
            $modified = true;
        }
    } else if ($event->eventtype == SCHEDULE_EVENT_TYPE_CLOSE) {
        // If the event is for the schedule activity closing then we should
        // set the end time of the schedule activity to be the new start
        // time of the event.
        if ($schedule->timeclose != $event->timestart) {
            $schedule->timeclose = $event->timestart;
            $modified = true;
        }
    }

    if ($modified) {
        $schedule->timemodified = time();
        // Persist the instance changes.
        $DB->update_record('schedule', $schedule);
        $event = \core\event\course_module_updated::create_from_cm($coursemodule, $context);
        $event->trigger();
    }
}

/**
 * Get icon mapping for font-awesome.
 */
function mod_schedule_get_fontawesome_icon_map() {
    return [
        'mod_schedule:row' => 'fa-info',
        'mod_schedule:column' => 'fa-columns',
    ];
}

/**
 * Add a get_coursemodule_info function in case any schedule type wants to add 'extra' information
 * for the course (see resource).
 *
 * Given a course_module object, this function returns any "extra" information that may be needed
 * when printing this activity in a course listing.  See get_array_of_activities() in course/lib.php.
 *
 * @param stdClass $coursemodule The coursemodule object (record).
 * @return cached_cm_info An object on information that the courses
 *                        will know about (most noticeably, an icon).
 */
function schedule_get_coursemodule_info($coursemodule) {
    global $DB;

    $dbparams = ['id' => $coursemodule->instance];
    $fields = 'id, name, intro, introformat, completionsubmit, timeopen, timeclose';
    if (!$schedule = $DB->get_record('schedule', $dbparams, $fields)) {
        return false;
    }

    $result = new cached_cm_info();
    $result->name = $schedule->name;

    if ($coursemodule->showdescription) {
        // Convert intro to html. Do not filter cached version, filters run at display time.
        $result->content = format_module_intro('schedule', $schedule, $coursemodule->id, false);
    }

    // Populate the custom completion rules as key => value pairs, but only if the completion mode is 'automatic'.
    if ($coursemodule->completion == COMPLETION_TRACKING_AUTOMATIC) {
        $result->customdata['customcompletionrules']['completionsubmit'] = $schedule->completionsubmit;
    }
    // Populate some other values that can be used in calendar or on dashboard.
    if ($schedule->timeopen) {
        $result->customdata['timeopen'] = $schedule->timeopen;
    }
    if ($schedule->timeclose) {
        $result->customdata['timeclose'] = $schedule->timeclose;
    }

    return $result;
}

/**
 * Callback which returns human-readable strings describing the active completion custom rules for the module instance.
 *
 * @param cm_info|stdClass $cm object with fields ->completion and ->customdata['customcompletionrules']
 * @return array $descriptions the array of descriptions for the custom rules.
 */
function mod_schedule_get_completion_active_rule_descriptions($cm) {
    // Values will be present in cm_info, and we assume these are up to date.
    if (empty($cm->customdata['customcompletionrules'])
        || $cm->completion != COMPLETION_TRACKING_AUTOMATIC) {
        return [];
    }

    $descriptions = [];
    foreach ($cm->customdata['customcompletionrules'] as $key => $val) {
        switch ($key) {
            case 'completionsubmit':
                if (!empty($val)) {
                    $descriptions[] = get_string('completionsubmit', 'schedule');
                }
                break;
            default:
                break;
        }
    }
    return $descriptions;
}
