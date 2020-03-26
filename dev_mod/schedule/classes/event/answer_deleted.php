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
 * The mod_schedule answer deleted event.
 *
 * @package    mod_schedule
 * @copyright  2016 Stephen Bourget
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_schedule\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The mod_schedule answer deleted event class.
 *
 * @property-read array $other {
 *      Extra information about event.
 *
 *      - int scheduleid: id of schedule.
 *      - int optionid: id of the option.
 * }
 *
 * @package    mod_schedule
 * @since      Moodle 3.1
 * @copyright  2016 Stephen Bourget
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class answer_deleted extends \core\event\base {

    /**
     * Creates an instance of the event from the records
     *
     * @param stdClass $scheduleanswer record from 'schedule_answers' table
     * @param stdClass $schedule record from 'schedule' table
     * @param stdClass $cm record from 'course_modules' table
     * @param stdClass $course
     * @return self
     */
    public static function create_from_object($scheduleanswer, $schedule, $cm, $course) {
        global $USER;
        $eventdata = array();
        $eventdata['objectid'] = $scheduleanswer->id;
        $eventdata['context'] = \context_module::instance($cm->id);
        $eventdata['userid'] = $USER->id;
        $eventdata['courseid'] = $course->id;
        $eventdata['relateduserid'] = $scheduleanswer->userid;
        $eventdata['other'] = array();
        $eventdata['other']['scheduleid'] = $schedule->id;
        $eventdata['other']['optionid'] = $scheduleanswer->optionid;
        $event = self::create($eventdata);
        $event->add_record_snapshot('course', $course);
        $event->add_record_snapshot('course_modules', $cm);
        $event->add_record_snapshot('schedule', $schedule);
        $event->add_record_snapshot('schedule_answers', $scheduleanswer);
        return $event;
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' has deleted the option with id '" . $this->other['optionid'] . "' for the
            user with id '$this->relateduserid' from the schedule activity with course module id '$this->contextinstanceid'.";
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventanswerdeleted', 'mod_schedule');
    }

    /**
     * Get URL related to the action
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/mod/schedule/view.php', array('id' => $this->contextinstanceid));
    }

    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['objecttable'] = 'schedule_answers';
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception
     * @return void
     */
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['scheduleid'])) {
            throw new \coding_exception('The \'scheduleid\' value must be set in other.');
        }

        if (!isset($this->other['optionid'])) {
            throw new \coding_exception('The \'optionid\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'schedule_answers', 'restore' => \core\event\base::NOT_MAPPED);
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['scheduleid'] = array('db' => 'schedule', 'restore' => 'schedule');
        $othermapped['optionid'] = array('db' => 'schedule_options', 'restore' => 'schedule_option');

        return $othermapped;
    }
}
