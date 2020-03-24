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
 * The mod_schedule report viewed event.
 *
 * @package mod_schedule
 * @copyright 2016 Stephen Bourget
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_schedule\event;
defined('MOODLE_INTERNAL') || die();

/**
 * The mod_schedule report viewed event class.
 *
 * @property-read array $other {
 *      Extra information about the event.
 *
 *      - string content: The content we are viewing.
 *      - string format: The report format
 *      - int scheduled: The id of the schedule
 * }
 *
 * @package    mod_schedule
 * @since      Moodle 3.1
 * @copyright  2016 Stephen Bourget
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_downloaded extends \core\event\base {

    /**
     * Init method.
     */
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    /**
     * Returns localised general event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventreportdownloaded', 'mod_schedule');
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' has downloaded the report in the '".$this->other['format']."' format for
            the schedule activity with course module id '$this->contextinstanceid'";
    }

    /**
     * Returns relevant URL.
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/mod/schedule/report.php', array('id' => $this->contextinstanceid));
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception
     * @return void
     */
    protected function validate_data() {
        parent::validate_data();

        // Report format downloaded.
        if (!isset($this->other['content'])) {
            throw new \coding_exception('The \'content\' value must be set in other.');
        }
        // Report format downloaded.
        if (!isset($this->other['format'])) {
            throw new \coding_exception('The \'format\' value must be set in other.');
        }
        // ID of the schedule activity.
        if (!isset($this->other['scheduleid'])) {
            throw new \coding_exception('The \'scheduleid\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return false;
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['scheduleid'] = array('db' => 'schedule', 'restore' => 'schedule');

        return $othermapped;
    }
}
