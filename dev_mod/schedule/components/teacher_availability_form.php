<?php
require_once(dirname(__FILE__).'/../../../config.php');
require_once($CFG->libdir.'/formslib.php');


class mod_schedule_teacher_availability_form extends moodleform {

    /**
    */
    public function definition() {
        global $CFG, $USER;

        $this->add_course_id();
        $this->create_date_selector();
        $this->create_time_selector();
        $this->create_bottom_buttons();
    }


    private function add_course_id() {
        $mform =& $this->_form;
        // $cmid = $this->_customdata['id'];
        // $mform->addElement('hidden', 'cmid', $cmid);
        // $mform->setType('cmid', PARAM_INT);

    }

    /**
    */
    private function create_date_selector () {
        $mform =& $this->_form;
        $mform->addElement(
            'date_selector', 
            'class_date', 
            get_string('sessiondate', 'attendance'));
    }


    /**
    */
    private function create_time_selector () {
        $mform =& $this->_form;

        for ($i = 0; $i <= 23; $i++) {
            $hours[$i] = sprintf("%02d", $i);
        }

        for ($i = 0; $i < 60; $i += 5) {
            $minutes[$i] = sprintf("%02d", $i);
        }

        $class_time = array();
        $class_time[] =& $mform->createElement('static', 'from', '', get_string('from', 'attendance'));
        $class_time[] =& $mform->createElement('select', 'start_hour', get_string('hour', 'form'), $hours, false, true);
        $class_time[] =& $mform->createElement('select', 'start_minute', get_string('minute', 'form'), $minutes, false, true);
        $class_time[] =& $mform->createElement('static', 'to', '', get_string('to', 'attendance'));
        $class_time[] =& $mform->createElement('select', 'end_hour', get_string('hour', 'form'), $hours, false, true);
        $class_time[] =& $mform->createElement('select', 'end_minute', get_string('minute', 'form'), $minutes, false, true);

        $mform->addGroup($class_time, 'class_time', get_string('time', 'attendance'), array(' '), true);
    }

    /**
    */
    private function create_bottom_buttons() {
        $mform =& $this->_form;

        $buttonarray=array();
        $buttonarray[] =& $mform->createElement('submit', 'submitbutton', get_string('savechanges'));
        $buttonarray[] =& $mform->createElement('submit', 'cancel', get_string('cancel'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), true);
    }
}