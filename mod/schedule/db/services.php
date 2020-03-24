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
 * Schedule external functions and service definitions.
 *
 * @package    mod_schedule
 * @category   external
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */

defined('MOODLE_INTERNAL') || die;

$functions = array(

    'mod_schedule_get_schedule_results' => array(
        'classname'     => 'mod_schedule_external',
        'methodname'    => 'get_schedule_results',
        'description'   => 'Retrieve users results for a given schedule.',
        'type'          => 'read',
        'capabilities'  => '',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_schedule_get_schedule_options' => array(
        'classname'     => 'mod_schedule_external',
        'methodname'    => 'get_schedule_options',
        'description'   => 'Retrieve options for a specific schedule.',
        'type'          => 'read',
        'capabilities'  => 'mod/schedule:choose',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_schedule_submit_schedule_response' => array(
        'classname'     => 'mod_schedule_external',
        'methodname'    => 'submit_schedule_response',
        'description'   => 'Submit responses to a specific schedule item.',
        'type'          => 'write',
        'capabilities'  => 'mod/schedule:choose',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_schedule_view_schedule' => array(
        'classname'     => 'mod_schedule_external',
        'methodname'    => 'view_schedule',
        'description'   => 'Trigger the course module viewed event and update the module completion status.',
        'type'          => 'write',
        'capabilities'  => '',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_schedule_get_schedules_by_courses' => array(
        'classname'     => 'mod_schedule_external',
        'methodname'    => 'get_schedules_by_courses',
        'description'   => 'Returns a list of schedule instances in a provided set of courses,
                            if no courses are provided then all the schedule instances the user has access to will be returned.',
        'type'          => 'read',
        'capabilities'  => '',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_schedule_delete_schedule_responses' => array(
        'classname'     => 'mod_schedule_external',
        'methodname'    => 'delete_schedule_responses',
        'description'   => 'Delete the given submitted responses in a schedule',
        'type'          => 'write',
        'capabilities'  => 'mod/schedule:choose',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),
);
