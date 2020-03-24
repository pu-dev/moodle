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
 * Internal library of functions for schedule module.
 *
 * All the schedule specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package   mod_schedule
 * @copyright 2016 Stephen Bourget
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * This creates new calendar events given as timeopen and timeclose by $schedule.
 *
 * @param stdClass $schedule
 * @return void
 */
function schedule_set_events($schedule) {
    global $DB, $CFG;

    require_once($CFG->dirroot.'/calendar/lib.php');

    // Get CMID if not sent as part of $schedule.
    if (!isset($schedule->coursemodule)) {
        $cm = get_coursemodule_from_instance('schedule', $schedule->id, $schedule->course);
        $schedule->coursemodule = $cm->id;
    }

    // Schedule start calendar events.
    $event = new stdClass();
    $event->eventtype = SCHEDULE_EVENT_TYPE_OPEN;
    // The SCHEDULE_EVENT_TYPE_OPEN event should only be an action event if no close time is specified.
    $event->type = empty($schedule->timeclose) ? CALENDAR_EVENT_TYPE_ACTION : CALENDAR_EVENT_TYPE_STANDARD;
    if ($event->id = $DB->get_field('event', 'id',
            array('modulename' => 'schedule', 'instance' => $schedule->id, 'eventtype' => $event->eventtype))) {
        if ((!empty($schedule->timeopen)) && ($schedule->timeopen > 0)) {
            // Calendar event exists so update it.
            $event->name         = get_string('calendarstart', 'schedule', $schedule->name);
            $event->description  = format_module_intro('schedule', $schedule, $schedule->coursemodule);
            $event->timestart    = $schedule->timeopen;
            $event->timesort     = $schedule->timeopen;
            $event->visible      = instance_is_visible('schedule', $schedule);
            $event->timeduration = 0;
            $calendarevent = calendar_event::load($event->id);
            $calendarevent->update($event, false);
        } else {
            // Calendar event is on longer needed.
            $calendarevent = calendar_event::load($event->id);
            $calendarevent->delete();
        }
    } else {
        // Event doesn't exist so create one.
        if ((!empty($schedule->timeopen)) && ($schedule->timeopen > 0)) {
            $event->name         = get_string('calendarstart', 'schedule', $schedule->name);
            $event->description  = format_module_intro('schedule', $schedule, $schedule->coursemodule);
            $event->courseid     = $schedule->course;
            $event->groupid      = 0;
            $event->userid       = 0;
            $event->modulename   = 'schedule';
            $event->instance     = $schedule->id;
            $event->timestart    = $schedule->timeopen;
            $event->timesort     = $schedule->timeopen;
            $event->visible      = instance_is_visible('schedule', $schedule);
            $event->timeduration = 0;
            calendar_event::create($event, false);
        }
    }

    // Schedule end calendar events.
    $event = new stdClass();
    $event->type = CALENDAR_EVENT_TYPE_ACTION;
    $event->eventtype = SCHEDULE_EVENT_TYPE_CLOSE;
    if ($event->id = $DB->get_field('event', 'id',
            array('modulename' => 'schedule', 'instance' => $schedule->id, 'eventtype' => $event->eventtype))) {
        if ((!empty($schedule->timeclose)) && ($schedule->timeclose > 0)) {
            // Calendar event exists so update it.
            $event->name         = get_string('calendarend', 'schedule', $schedule->name);
            $event->description  = format_module_intro('schedule', $schedule, $schedule->coursemodule);
            $event->timestart    = $schedule->timeclose;
            $event->timesort     = $schedule->timeclose;
            $event->visible      = instance_is_visible('schedule', $schedule);
            $event->timeduration = 0;
            $calendarevent = calendar_event::load($event->id);
            $calendarevent->update($event, false);
        } else {
            // Calendar event is on longer needed.
            $calendarevent = calendar_event::load($event->id);
            $calendarevent->delete();
        }
    } else {
        // Event doesn't exist so create one.
        if ((!empty($schedule->timeclose)) && ($schedule->timeclose > 0)) {
            $event->name         = get_string('calendarend', 'schedule', $schedule->name);
            $event->description  = format_module_intro('schedule', $schedule, $schedule->coursemodule);
            $event->courseid     = $schedule->course;
            $event->groupid      = 0;
            $event->userid       = 0;
            $event->modulename   = 'schedule';
            $event->instance     = $schedule->id;
            $event->timestart    = $schedule->timeclose;
            $event->timesort     = $schedule->timeclose;
            $event->visible      = instance_is_visible('schedule', $schedule);
            $event->timeduration = 0;
            calendar_event::create($event, false);
        }
    }
}
