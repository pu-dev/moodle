<?php namespace mod_schedule;
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../inc.php');
require_once($CFG->libdir.'/formslib.php');


abstract class form_base extends \moodleform {
    const MINUTES_IN_HOUR = 60;
    const SECONDS_IN_MINUTE = 60;

    protected $header_selector;

    protected function create_time_selector ($element_name) {
        $mform =& $this->_form;

        for ($i = 0; $i <= 23; $i++) {
            $hours[$i] = sprintf("%02d", $i);
        }

        for ($i = 0; $i < 60; $i += 5) {
            $minutes[$i] = sprintf("%02d", $i);
        }

        $time = array();
        $time[] =& $mform->createElement('static', 'from', '', 'from ');
        $time[] =& $mform->createElement('html', '<div style="width: 5px"></div>', '', '');
        $time[] =& $mform->createElement('select', 'start_hour', get_string('hour', 'form'), $hours, false, true);
        $time[] =& $mform->createElement('select', 'start_minute', get_string('minute', 'form'), $minutes, false, true);
        $time[] =& $mform->createElement('html', '<div style="width: 10px"></div>', '', '');
        $time[] =& $mform->createElement('static', 'to', '', 'to ');
        $time[] =& $mform->createElement('html', '<div style="width: 5px"></div>', '', '');
        $time[] =& $mform->createElement('select', 'end_hour', get_string('hour', 'form'), $hours, false, true);
        $time[] =& $mform->createElement('select', 'end_minute', get_string('minute', 'form'), $minutes, false, true);

        $mform->addGroup($time, $element_name, get_string('time', 'attendance'), array(' '), true);
    }

    protected function create_header($title, $selector) {
        $this->header_selector = $selector;

        $mform =& $this->_form;
        $mform->addElement(
            'header', 
            $selector, 
            $title);
        $mform->setExpanded($selector, false);
    }

    protected function create_week_days($days_selector) {
        $mform =& $this->_form;
        $days = array();

        $sdays[] =& $mform->createElement('checkbox', 1, '', get_string('monday', 'calendar'));
        $sdays[] =& $mform->createElement('checkbox', 2, '', get_string('tuesday', 'calendar'));
        $sdays[] =& $mform->createElement('checkbox', 3, '', get_string('wednesday', 'calendar'));
        $sdays[] =& $mform->createElement('checkbox', 4, '', get_string('thursday', 'calendar'));
        $sdays[] =& $mform->createElement('checkbox', 5, '', get_string('friday', 'calendar'));
        $sdays[] =& $mform->createElement('checkbox', 6, '', get_string('saturday', 'calendar'));
        $sdays[] =& $mform->createElement('checkbox', 7, '', get_string('sunday', 'calendar'));

        $elements_spacer = '&nbsp;&nbsp;&nbsp;&nbsp;';
        $mform->addGroup(
            $sdays, 
            $days_selector, 
            # todo 
            'Repeat on',
            $elements_spacer, 
            true);
    }

    protected function create_save_button() {
        $mform =& $this->_form;
        $buttonarray=array();
        $buttonarray[] =& $mform->createElement('submit', 'submitbutton', get_string('savechanges'));
        // $buttonarray[] =& $mform->createElement('cancel', 'cancelbutton', get_string('cancel'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
    }


    protected function validate_class_time(& $errors, $data, $time_selector) {
        $lesson_time = $data[$time_selector];
        $start_time = $lesson_time['start_hour'] * self::MINUTES_IN_HOUR + $lesson_time['start_minute'];
        $end_time = $lesson_time['end_hour'] * self::MINUTES_IN_HOUR + $lesson_time['end_minute'];

        if ($start_time >= $end_time) {
            #todo 
            $errors[$time_selector] = "Start time must be before ed time";
        }
    }

    protected function validate_class_start_date(
        & $errors, 
        $data, 
        $date_selector,
        $time_selector) 
    {
        $lesson_time = $data[$time_selector];
        $lesson_date = $data[$date_selector];

        $date = tools::get_epoch_date(
            $lesson_date,
            $lesson_time['start_hour'],
            $lesson_time['start_minute'] 
        );

        if ( $date < time() ) {
            $errors[$date_selector] = 'This date has passed';
        }
    }

    protected function validate_class_end_date(
        & $errors, 
        $data, 
        $date_start_selector,
        $date_end_selector) 
    {
        $date_start = $data[$date_start_selector];
        $date_end = $data[$date_end_selector];

        if ( $date_end < $date_start ) {
            $errors[$date_end_selector] = 'This date has to be before start date';
        }
    }

    protected function validate_class_days(
        & $errors,
        $data, 
        $days_selector) 
    {
        $days = $data[$days_selector];
        debug('daus:'.$days.is_null($days));
        if ( is_null($days) ) {
            $errors[$days_selector] = 'Select at least one week day';
        }
    }

    public function set_expanded($expanded) {
        if ( is_null($this->header_selector) ) {
            return;
        }

        $this->_form->setExpanded($this->header_selector, $expanded);
    }
}