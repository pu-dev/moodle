<?php
require_once('./debug.php');
require_once($CFG->libdir.'/formslib.php');


class mod_schedule_add_teacher_availability_form extends moodleform {

    /**
    */
    public function definition() {
        global $CFG, $USER;
        $mform =& $this->_form;

        // $course_id = $this->_customdata['course_id'];

        $this->createDateSelector();
        $this->createTimeSelector();
        $this->createBottomButtons();
    }


    /**
    */
    private function createDateSelector () {
        $mform =& $this->_form;
        $mform->addElement(
            'date_selector', 
            'sessiondate', 
            get_string('sessiondate', 'attendance'));
    }


    /**
    */
    private function createTimeSelector () {
        $mform =& $this->_form;

        for ($i = 0; $i <= 23; $i++) {
            $hours[$i] = sprintf("%02d", $i);
        }

        for ($i = 0; $i < 60; $i += 5) {
            $minutes[$i] = sprintf("%02d", $i);
        }

        $sesendtime = array();
        $sesendtime[] =& $mform->createElement('static', 'from', '', get_string('from', 'attendance'));
        $sesendtime[] =& $mform->createElement('select', 'starthour', get_string('hour', 'form'), $hours, false, true);
        $sesendtime[] =& $mform->createElement('select', 'startminute', get_string('minute', 'form'), $minutes, false, true);
        $sesendtime[] =& $mform->createElement('static', 'to', '', get_string('to', 'attendance'));
        $sesendtime[] =& $mform->createElement('select', 'endhour', get_string('hour', 'form'), $hours, false, true);
        $sesendtime[] =& $mform->createElement('select', 'endminute', get_string('minute', 'form'), $minutes, false, true);

        $mform->addGroup($sesendtime, 'sestime', get_string('time', 'attendance'), array(' '), true);
    }

    /**
    */
    private function createBottomButtons() {
        $mform =& $this->_form;

        $buttonarray=array();
        $buttonarray[] =& $mform->createElement('submit', 'submitbutton', get_string('savechanges'));
        $buttonarray[] =& $mform->createElement('submit', 'cancel', get_string('cancel'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), true);
    }

}