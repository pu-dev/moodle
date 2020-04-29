<?php

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

function schedule_add_instance($schedule) {
    global $DB;

    $intro = '';
    $introformat = 1;

    if ( isset($schedule->introeditor) ) {
        $intro = $schedule->introeditor['text'];
        $introformat = $schedule->introeditor['format'];
    }

    $schedule_fixed = new stdClass;
    $schedule_fixed->course = $schedule->course;
    $schedule_fixed->name = $schedule->name;
    $schedule_fixed->intro = $intro;
    $schedule_fixed->introformat = $introformat;
    $schedule_fixed->timemodified = time();
    $schedule_fixed->lesson_limit_value = $schedule->lesson_limit_value;
    $schedule_fixed->lesson_limit_period = $schedule->lesson_limit_period;

    $schedule_fixed->id = $DB->insert_record(
        "schedule", 
        $schedule_fixed);

    return $schedule_fixed->id;
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
    global $DB;

    $schedule->timemodified = time();
    $schedule->id = $schedule->instance;

    // $schedule_fixed = __get_schedule_db_record($schedule);
    // $schedule_fixed->id = $schedule->instance;
    return $DB->update_record(
        "schedule",
        $schedule);
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

# todo
function schedule_delete_instance($id) {
    error_log("delteet");
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



function schedule_get_schedule($schedule_id) {
    global $DB;
    
    $schedule = $DB->get_record("schedule", array("id" => $schedule_id));
    if ( $schedule ) {
        return $schedule;
    }
    return false;
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
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return false;
        case FEATURE_COMPLETION_HAS_RULES:    return false;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return false;
        case FEATURE_SHOW_DESCRIPTION:        return true;

        default: return null;
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
        // schedule_set_events($instance);
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
        // schedule_set_events($schedule);
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
    $fields = 'id, name, intro, introformat';
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
    // if ($coursemodule->completion == COMPLETION_TRACKING_AUTOMATIC) {
    //     $result->customdata['customcompletionrules']['completionsubmit'] = $schedule->completionsubmit;
    // }
    // // Populate some other values that can be used in calendar or on dashboard.
    // if ($schedule->timeopen) {
    //     $result->customdata['timeopen'] = $schedule->timeopen;
    // }
    // if ($schedule->timeclose) {
    //     $result->customdata['timeclose'] = $schedule->timeclose;
    // }

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





define('VIEW_ALL', 1);
define('VIEW_MONTH', 2);
define('VIEW_WEEK', 3);


//-------------------
