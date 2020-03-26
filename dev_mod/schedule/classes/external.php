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
 * Schedule module external API
 *
 * @package    mod_schedule
 * @category   external
 * @copyright  2015 Costantino Cito <ccito@cvaconsulting.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */

defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/mod/schedule/lib.php');

/**
 * Schedule module external functions
 *
 * @package    mod_schedule
 * @category   external
 * @copyright  2015 Costantino Cito <ccito@cvaconsulting.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */
class mod_schedule_external extends external_api {

    /**
     * Describes the parameters for get_schedules_by_courses.
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function get_schedule_results_parameters() {
        return new external_function_parameters (array('scheduleid' => new external_value(PARAM_INT, 'schedule instance id')));
    }
    /**
     * Returns user's results for a specific schedule
     * and a list of those users that did not answered yet.
     *
     * @param int $scheduleid the schedule instance id
     * @return array of responses details
     * @since Moodle 3.0
     */
    public static function get_schedule_results($scheduleid) {
        global $USER, $PAGE;

        $params = self::validate_parameters(self::get_schedule_results_parameters(), array('scheduleid' => $scheduleid));

        if (!$schedule = schedule_get_schedule($params['scheduleid'])) {
            throw new moodle_exception("invalidcoursemodule", "error");
        }
        list($course, $cm) = get_course_and_cm_from_instance($schedule, 'schedule');

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        $groupmode = groups_get_activity_groupmode($cm);
        // Check if we have to include responses from inactive users.
        $onlyactive = $schedule->includeinactive ? false : true;
        $users = schedule_get_response_data($schedule, $cm, $groupmode, $onlyactive);
        // Show those who haven't answered the question.
        if (!empty($schedule->showunanswered)) {
            $schedule->option[0] = get_string('notanswered', 'schedule');
            $schedule->maxanswers[0] = 0;
        }
        $results = prepare_schedule_show_results($schedule, $course, $cm, $users);

        $options = array();
        $fullnamecap = has_capability('moodle/site:viewfullnames', $context);
        foreach ($results->options as $optionid => $option) {

            $userresponses = array();
            $numberofuser = 0;
            $percentageamount = 0;
            if (property_exists($option, 'user') and
                (has_capability('mod/schedule:readresponses', $context) or schedule_can_view_results($schedule))) {
                $numberofuser = count($option->user);
                $percentageamount = ((float)$numberofuser / (float)$results->numberofuser) * 100.0;
                if ($schedule->publish) {
                    foreach ($option->user as $userresponse) {
                        $response = array();
                        $response['userid'] = $userresponse->id;
                        $response['fullname'] = fullname($userresponse, $fullnamecap);

                        $userpicture = new user_picture($userresponse);
                        $userpicture->size = 1; // Size f1.
                        $response['profileimageurl'] = $userpicture->get_url($PAGE)->out(false);

                        // Add optional properties.
                        foreach (array('answerid', 'timemodified') as $field) {
                            if (property_exists($userresponse, 'answerid')) {
                                $response[$field] = $userresponse->$field;
                            }
                        }
                        $userresponses[] = $response;
                    }
                }
            }

            $options[] = array('id'               => $optionid,
                               'text'             => external_format_string($option->text, $context->id),
                               'maxanswer'        => $option->maxanswer,
                               'userresponses'    => $userresponses,
                               'numberofuser'     => $numberofuser,
                               'percentageamount' => $percentageamount
                              );
        }

        $warnings = array();
        return array(
            'options' => $options,
            'warnings' => $warnings
        );
    }

    /**
     * Describes the get_schedule_results return value.
     *
     * @return external_single_structure
     * @since Moodle 3.0
     */
    public static function get_schedule_results_returns() {
        return new external_single_structure(
            array(
                'options' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'schedule instance id'),
                            'text' => new external_value(PARAM_RAW, 'text of the schedule'),
                            'maxanswer' => new external_value(PARAM_INT, 'maximum number of answers'),
                            'userresponses' => new external_multiple_structure(
                                 new external_single_structure(
                                     array(
                                        'userid' => new external_value(PARAM_INT, 'user id'),
                                        'fullname' => new external_value(PARAM_NOTAGS, 'user full name'),
                                        'profileimageurl' => new external_value(PARAM_URL, 'profile user image url'),
                                        'answerid' => new external_value(PARAM_INT, 'answer id', VALUE_OPTIONAL),
                                        'timemodified' => new external_value(PARAM_INT, 'time of modification', VALUE_OPTIONAL),
                                     ), 'User responses'
                                 )
                            ),
                            'numberofuser' => new external_value(PARAM_INT, 'number of users answers'),
                            'percentageamount' => new external_value(PARAM_FLOAT, 'percentage of users answers')
                        ), 'Options'
                    )
                ),
                'warnings' => new external_warnings(),
            )
        );
    }

    /**
     * Describes the parameters for mod_schedule_get_schedule_options.
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function get_schedule_options_parameters() {
        return new external_function_parameters (array('scheduleid' => new external_value(PARAM_INT, 'schedule instance id')));
    }

    /**
     * Returns options for a specific schedule
     *
     * @param int $scheduleid the schedule instance id
     * @return array of options details
     * @since Moodle 3.0
     */
    public static function get_schedule_options($scheduleid) {
        global $USER;
        $warnings = array();
        $params = self::validate_parameters(self::get_schedule_options_parameters(), array('scheduleid' => $scheduleid));

        if (!$schedule = schedule_get_schedule($params['scheduleid'])) {
            throw new moodle_exception("invalidcoursemodule", "error");
        }
        list($course, $cm) = get_course_and_cm_from_instance($schedule, 'schedule');

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        require_capability('mod/schedule:choose', $context);

        $groupmode = groups_get_activity_groupmode($cm);
        $onlyactive = $schedule->includeinactive ? false : true;
        $allresponses = schedule_get_response_data($schedule, $cm, $groupmode, $onlyactive);

        $timenow = time();
        $scheduleopen = true;
        $showpreview = false;

        if (!empty($schedule->timeopen) && ($schedule->timeopen > $timenow)) {
            $scheduleopen = false;
            $warnings[1] = get_string("notopenyet", "schedule", userdate($schedule->timeopen));
            if ($schedule->showpreview) {
                $warnings[2] = get_string('previewonly', 'schedule', userdate($schedule->timeopen));
                $showpreview = true;
            }
        }
        if (!empty($schedule->timeclose) && ($timenow > $schedule->timeclose)) {
            $scheduleopen = false;
            $warnings[3] = get_string("expired", "schedule", userdate($schedule->timeclose));
        }

        $optionsarray = array();

        if ($scheduleopen or $showpreview) {

            $options = schedule_prepare_options($schedule, $USER, $cm, $allresponses);

            foreach ($options['options'] as $option) {
                $optionarr = array();
                $optionarr['id']            = $option->attributes->value;
                $optionarr['text']          = external_format_string($option->text, $context->id);
                $optionarr['maxanswers']    = $option->maxanswers;
                $optionarr['displaylayout'] = $option->displaylayout;
                $optionarr['countanswers']  = $option->countanswers;
                foreach (array('checked', 'disabled') as $field) {
                    if (property_exists($option->attributes, $field) and $option->attributes->$field == 1) {
                        $optionarr[$field] = 1;
                    } else {
                        $optionarr[$field] = 0;
                    }
                }
                // When showpreview is active, we show options as disabled.
                if ($showpreview or ($optionarr['checked'] == 1 and !$schedule->allowupdate)) {
                    $optionarr['disabled'] = 1;
                }
                $optionsarray[] = $optionarr;
            }
        }
        foreach ($warnings as $key => $message) {
            $warnings[$key] = array(
                'item' => 'schedule',
                'itemid' => $cm->id,
                'warningcode' => $key,
                'message' => $message
            );
        }
        return array(
            'options' => $optionsarray,
            'warnings' => $warnings
        );
    }

    /**
     * Describes the get_schedule_results return value.
     *
     * @return external_multiple_structure
     * @since Moodle 3.0
     */
    public static function get_schedule_options_returns() {
        return new external_single_structure(
            array(
                'options' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'option id'),
                            'text' => new external_value(PARAM_RAW, 'text of the schedule'),
                            'maxanswers' => new external_value(PARAM_INT, 'maximum number of answers'),
                            'displaylayout' => new external_value(PARAM_BOOL, 'true for orizontal, otherwise vertical'),
                            'countanswers' => new external_value(PARAM_INT, 'number of answers'),
                            'checked' => new external_value(PARAM_BOOL, 'we already answered'),
                            'disabled' => new external_value(PARAM_BOOL, 'option disabled'),
                            )
                    ), 'Options'
                ),
                'warnings' => new external_warnings(),
            )
        );
    }

    /**
     * Describes the parameters for submit_schedule_response.
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function submit_schedule_response_parameters() {
        return new external_function_parameters (
            array(
                'scheduleid' => new external_value(PARAM_INT, 'schedule instance id'),
                'responses' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'answer id'),
                    'Array of response ids'
                ),
            )
        );
    }

    /**
     * Submit schedule responses
     *
     * @param int $scheduleid the schedule instance id
     * @param array $responses the response ids
     * @return array answers information and warnings
     * @since Moodle 3.0
     */
    public static function submit_schedule_response($scheduleid, $responses) {
        global $USER;

        $warnings = array();
        $params = self::validate_parameters(self::submit_schedule_response_parameters(),
                                            array(
                                                'scheduleid' => $scheduleid,
                                                'responses' => $responses
                                            ));

        if (!$schedule = schedule_get_schedule($params['scheduleid'])) {
            throw new moodle_exception("invalidcoursemodule", "error");
        }
        list($course, $cm) = get_course_and_cm_from_instance($schedule, 'schedule');

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        require_capability('mod/schedule:choose', $context);

        $timenow = time();
        if (!empty($schedule->timeopen) && ($schedule->timeopen > $timenow)) {
            throw new moodle_exception("notopenyet", "schedule", '', userdate($schedule->timeopen));
        } else if (!empty($schedule->timeclose) && ($timenow > $schedule->timeclose)) {
            throw new moodle_exception("expired", "schedule", '', userdate($schedule->timeclose));
        }

        if (!schedule_get_my_response($schedule) or $schedule->allowupdate) {
            // When a single response is given, we convert the array to a simple variable
            // in order to avoid schedule_user_submit_response to check with allowmultiple even
            // for a single response.
            if (count($params['responses']) == 1) {
                $params['responses'] = reset($params['responses']);
            }
            schedule_user_submit_response($params['responses'], $schedule, $USER->id, $course, $cm);
        } else {
            throw new moodle_exception('missingrequiredcapability', 'webservice', '', 'allowupdate');
        }
        $answers = schedule_get_my_response($schedule);

        return array(
            'answers' => $answers,
            'warnings' => $warnings
        );
    }

    /**
     * Describes the submit_schedule_response return value.
     *
     * @return external_multiple_structure
     * @since Moodle 3.0
     */
    public static function submit_schedule_response_returns() {
        return new external_single_structure(
            array(
                'answers' => new external_multiple_structure(
                     new external_single_structure(
                         array(
                             'id'           => new external_value(PARAM_INT, 'answer id'),
                             'scheduleid'     => new external_value(PARAM_INT, 'scheduleid'),
                             'userid'       => new external_value(PARAM_INT, 'user id'),
                             'optionid'     => new external_value(PARAM_INT, 'optionid'),
                             'timemodified' => new external_value(PARAM_INT, 'time of last modification')
                         ), 'Answers'
                     )
                ),
                'warnings' => new external_warnings(),
            )
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function view_schedule_parameters() {
        return new external_function_parameters(
            array(
                'scheduleid' => new external_value(PARAM_INT, 'schedule instance id')
            )
        );
    }

    /**
     * Trigger the course module viewed event and update the module completion status.
     *
     * @param int $scheduleid the schedule instance id
     * @return array of warnings and status result
     * @since Moodle 3.0
     * @throws moodle_exception
     */
    public static function view_schedule($scheduleid) {
        global $CFG;

        $params = self::validate_parameters(self::view_schedule_parameters(),
                                            array(
                                                'scheduleid' => $scheduleid
                                            ));
        $warnings = array();

        // Request and permission validation.
        if (!$schedule = schedule_get_schedule($params['scheduleid'])) {
            throw new moodle_exception("invalidcoursemodule", "error");
        }
        list($course, $cm) = get_course_and_cm_from_instance($schedule, 'schedule');

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        // Trigger course_module_viewed event and completion.
        schedule_view($schedule, $course, $cm, $context);

        $result = array();
        $result['status'] = true;
        $result['warnings'] = $warnings;
        return $result;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 3.0
     */
    public static function view_schedule_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'warnings' => new external_warnings()
            )
        );
    }

    /**
     * Describes the parameters for get_schedules_by_courses.
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function get_schedules_by_courses_parameters() {
        return new external_function_parameters (
            array(
                'courseids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'course id'), 'Array of course ids', VALUE_DEFAULT, array()
                ),
            )
        );
    }

    /**
     * Returns a list of schedules in a provided list of courses,
     * if no list is provided all schedules that the user can view will be returned.
     *
     * @param array $courseids the course ids
     * @return array of schedules details
     * @since Moodle 3.0
     */
    public static function get_schedules_by_courses($courseids = array()) {
        global $CFG;

        $returnedschedules = array();
        $warnings = array();

        $params = self::validate_parameters(self::get_schedules_by_courses_parameters(), array('courseids' => $courseids));

        $courses = array();
        if (empty($params['courseids'])) {
            $courses = enrol_get_my_courses();
            $params['courseids'] = array_keys($courses);
        }

        // Ensure there are courseids to loop through.
        if (!empty($params['courseids'])) {

            list($courses, $warnings) = external_util::validate_courses($params['courseids'], $courses);

            // Get the schedules in this course, this function checks users visibility permissions.
            // We can avoid then additional validate_context calls.
            $schedules = get_all_instances_in_courses("schedule", $courses);
            foreach ($schedules as $schedule) {
                $context = context_module::instance($schedule->coursemodule);
                // Entry to return.
                $scheduledetails = array();
                // First, we return information that any user can see in the web interface.
                $scheduledetails['id'] = $schedule->id;
                $scheduledetails['coursemodule'] = $schedule->coursemodule;
                $scheduledetails['course'] = $schedule->course;
                $scheduledetails['name']  = external_format_string($schedule->name, $context->id);
                // Format intro.
                $options = array('noclean' => true);
                list($scheduledetails['intro'], $scheduledetails['introformat']) =
                    external_format_text($schedule->intro, $schedule->introformat, $context->id, 'mod_schedule', 'intro', null, $options);
                $scheduledetails['introfiles'] = external_util::get_area_files($context->id, 'mod_schedule', 'intro', false, false);

                if (has_capability('mod/schedule:choose', $context)) {
                    $scheduledetails['publish']  = $schedule->publish;
                    $scheduledetails['showresults']  = $schedule->showresults;
                    $scheduledetails['showpreview']  = $schedule->showpreview;
                    $scheduledetails['timeopen']  = $schedule->timeopen;
                    $scheduledetails['timeclose']  = $schedule->timeclose;
                    $scheduledetails['display']  = $schedule->display;
                    $scheduledetails['allowupdate']  = $schedule->allowupdate;
                    $scheduledetails['allowmultiple']  = $schedule->allowmultiple;
                    $scheduledetails['limitanswers']  = $schedule->limitanswers;
                    $scheduledetails['showunanswered']  = $schedule->showunanswered;
                    $scheduledetails['includeinactive']  = $schedule->includeinactive;
                }

                if (has_capability('moodle/course:manageactivities', $context)) {
                    $scheduledetails['timemodified']  = $schedule->timemodified;
                    $scheduledetails['completionsubmit']  = $schedule->completionsubmit;
                    $scheduledetails['section']  = $schedule->section;
                    $scheduledetails['visible']  = $schedule->visible;
                    $scheduledetails['groupmode']  = $schedule->groupmode;
                    $scheduledetails['groupingid']  = $schedule->groupingid;
                }
                $returnedschedules[] = $scheduledetails;
            }
        }
        $result = array();
        $result['schedules'] = $returnedschedules;
        $result['warnings'] = $warnings;
        return $result;
    }

    /**
     * Describes the mod_schedule_get_schedules_by_courses return value.
     *
     * @return external_single_structure
     * @since Moodle 3.0
     */
    public static function get_schedules_by_courses_returns() {
        return new external_single_structure(
            array(
                'schedules' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'Schedule instance id'),
                            'coursemodule' => new external_value(PARAM_INT, 'Course module id'),
                            'course' => new external_value(PARAM_INT, 'Course id'),
                            'name' => new external_value(PARAM_RAW, 'Schedule name'),
                            'intro' => new external_value(PARAM_RAW, 'The schedule intro'),
                            'introformat' => new external_format_value('intro'),
                            'introfiles' => new external_files('Files in the introduction text', VALUE_OPTIONAL),
                            'publish' => new external_value(PARAM_BOOL, 'If schedule is published', VALUE_OPTIONAL),
                            'showresults' => new external_value(PARAM_INT, '0 never, 1 after answer, 2 after close, 3 always',
                                                                VALUE_OPTIONAL),
                            'display' => new external_value(PARAM_INT, 'Display mode (vertical, horizontal)', VALUE_OPTIONAL),
                            'allowupdate' => new external_value(PARAM_BOOL, 'Allow update', VALUE_OPTIONAL),
                            'allowmultiple' => new external_value(PARAM_BOOL, 'Allow multiple schedules', VALUE_OPTIONAL),
                            'showunanswered' => new external_value(PARAM_BOOL, 'Show users who not answered yet', VALUE_OPTIONAL),
                            'includeinactive' => new external_value(PARAM_BOOL, 'Include inactive users', VALUE_OPTIONAL),
                            'limitanswers' => new external_value(PARAM_BOOL, 'Limit unswers', VALUE_OPTIONAL),
                            'timeopen' => new external_value(PARAM_INT, 'Date of opening validity', VALUE_OPTIONAL),
                            'timeclose' => new external_value(PARAM_INT, 'Date of closing validity', VALUE_OPTIONAL),
                            'showpreview' => new external_value(PARAM_BOOL, 'Show preview before timeopen', VALUE_OPTIONAL),
                            'timemodified' => new external_value(PARAM_INT, 'Time of last modification', VALUE_OPTIONAL),
                            'completionsubmit' => new external_value(PARAM_BOOL, 'Completion on user submission', VALUE_OPTIONAL),
                            'section' => new external_value(PARAM_INT, 'Course section id', VALUE_OPTIONAL),
                            'visible' => new external_value(PARAM_BOOL, 'Visible', VALUE_OPTIONAL),
                            'groupmode' => new external_value(PARAM_INT, 'Group mode', VALUE_OPTIONAL),
                            'groupingid' => new external_value(PARAM_INT, 'Group id', VALUE_OPTIONAL),
                        ), 'Schedules'
                    )
                ),
                'warnings' => new external_warnings(),
            )
        );
    }

    /**
     * Describes the parameters for delete_schedule_responses.
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function delete_schedule_responses_parameters() {
        return new external_function_parameters (
            array(
                'scheduleid' => new external_value(PARAM_INT, 'schedule instance id'),
                'responses' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'response id'),
                    'Array of response ids, empty for deleting all the current user responses.',
                    VALUE_DEFAULT,
                    array()
                ),
            )
        );
    }

    /**
     * Delete the given submitted responses in a schedule
     *
     * @param int $scheduleid the schedule instance id
     * @param array $responses the response ids,  empty for deleting all the current user responses
     * @return array status information and warnings
     * @throws moodle_exception
     * @since Moodle 3.0
     */
    public static function delete_schedule_responses($scheduleid, $responses = array()) {

        $status = false;
        $warnings = array();
        $params = self::validate_parameters(self::delete_schedule_responses_parameters(),
                                            array(
                                                'scheduleid' => $scheduleid,
                                                'responses' => $responses
                                            ));

        if (!$schedule = schedule_get_schedule($params['scheduleid'])) {
            throw new moodle_exception("invalidcoursemodule", "error");
        }
        list($course, $cm) = get_course_and_cm_from_instance($schedule, 'schedule');

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        require_capability('mod/schedule:choose', $context);

        $candeleteall = has_capability('mod/schedule:deleteresponses', $context);
        if ($candeleteall || $schedule->allowupdate) {

            // Check if we can delete our own responses.
            if (!$candeleteall) {
                $timenow = time();
                if (!empty($schedule->timeclose) && ($timenow > $schedule->timeclose)) {
                    throw new moodle_exception("expired", "schedule", '', userdate($schedule->timeclose));
                }
            }

            if (empty($params['responses'])) {
                // No responses indicated so delete only my responses.
                $todelete = array_keys(schedule_get_my_response($schedule));
            } else {
                // Fill an array with the responses that can be deleted for this schedule.
                if ($candeleteall) {
                    // Teacher/managers can delete any.
                    $allowedresponses = array_keys(schedule_get_all_responses($schedule));
                } else {
                    // Students can delete only their own responses.
                    $allowedresponses = array_keys(schedule_get_my_response($schedule));
                }

                $todelete = array();
                foreach ($params['responses'] as $response) {
                    if (!in_array($response, $allowedresponses)) {
                        $warnings[] = array(
                            'item' => 'response',
                            'itemid' => $response,
                            'warningcode' => 'nopermissions',
                            'message' => 'Invalid response id, the response does not exist or you are not allowed to delete it.'
                        );
                    } else {
                        $todelete[] = $response;
                    }
                }
            }

            $status = schedule_delete_responses($todelete, $schedule, $cm, $course);
        } else {
            // The user requires the capability to delete responses.
            throw new required_capability_exception($context, 'mod/schedule:deleteresponses', 'nopermissions', '');
        }

        return array(
            'status' => $status,
            'warnings' => $warnings
        );
    }

    /**
     * Describes the delete_schedule_responses return value.
     *
     * @return external_multiple_structure
     * @since Moodle 3.0
     */
    public static function delete_schedule_responses_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status, true if everything went right'),
                'warnings' => new external_warnings(),
            )
        );
    }

}
